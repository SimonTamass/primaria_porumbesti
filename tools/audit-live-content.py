#!/usr/bin/env python3
"""Compare the public and development Comuna Porumbești WordPress sites.

The audit uses the public WordPress REST API as the inventory, then fetches the
actual front-end URL for every page and post on both hosts.  Reports are written
to output/live-content-audit/.
"""

from __future__ import annotations

import csv
import html
import json
import re
import ssl
import sys
import time
import unicodedata
import urllib.error
import urllib.parse
import urllib.request
from collections import Counter
from concurrent.futures import ThreadPoolExecutor, as_completed
from dataclasses import asdict, dataclass
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path
from typing import Any, Iterable


PROD_ORIGIN = "https://primariaporumbesti.ro"
DEV_ORIGIN = "https://dev.primariaporumbesti.ro"
OUTPUT_DIR = Path(__file__).resolve().parents[1] / "output" / "live-content-audit"
USER_AGENT = "Mozilla/5.0 (compatible; Codex-PrimariaPorumbesti-Content-Audit/1.0)"
MAX_WORKERS = 10
TIMEOUT_SECONDS = 45
RETRIES = 3

TEXT_TAG_BREAKS = {
    "address",
    "article",
    "blockquote",
    "br",
    "caption",
    "dd",
    "div",
    "dl",
    "dt",
    "figcaption",
    "figure",
    "h1",
    "h2",
    "h3",
    "h4",
    "h5",
    "h6",
    "hr",
    "li",
    "ol",
    "p",
    "section",
    "table",
    "tbody",
    "td",
    "tfoot",
    "th",
    "thead",
    "tr",
    "ul",
}
SKIP_TAGS = {"script", "style", "noscript", "svg", "template"}
ASSET_EXTENSIONS = {
    ".avif",
    ".bmp",
    ".csv",
    ".doc",
    ".docx",
    ".gif",
    ".jpeg",
    ".jpg",
    ".ods",
    ".odt",
    ".pdf",
    ".png",
    ".ppt",
    ".pptx",
    ".rar",
    ".svg",
    ".tif",
    ".tiff",
    ".webp",
    ".xls",
    ".xlsx",
    ".zip",
}
IMAGE_EXTENSIONS = {
    ".avif",
    ".bmp",
    ".gif",
    ".jpeg",
    ".jpg",
    ".png",
    ".svg",
    ".tif",
    ".tiff",
    ".webp",
}


@dataclass
class FetchResult:
    url: str
    status: int | None
    final_url: str
    content_type: str
    body: str
    error: str
    elapsed_ms: int


@dataclass
class AuditRow:
    item_type: str
    item_id: int
    date: str
    modified: str
    title: str
    slug: str
    prod_url: str
    dev_url: str
    dev_canonical_url: str
    prod_status: int | None
    dev_status: int | None
    prod_final_url: str
    dev_final_url: str
    prod_tokens: int
    dev_tokens: int
    content_recall: float
    content_precision: float
    prod_assets: int
    dev_assets: int
    missing_assets: str
    missing_asset_count: int
    prod_links: int
    dev_links: int
    missing_links: str
    missing_link_count: int
    dynamic_listing: bool
    title_present: bool
    severity: str
    issue: str


class VisibleHTMLParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.parts: list[str] = []
        self.urls: list[str] = []
        self.skip_depth = 0

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        tag = tag.casefold()
        if tag in SKIP_TAGS:
            self.skip_depth += 1
            return
        if self.skip_depth:
            return
        attr_map = dict(attrs)
        for key in ("href", "src", "data-src", "data-lazy-src"):
            value = attr_map.get(key)
            if value:
                self.urls.append(value)
        for key in ("srcset", "data-srcset"):
            value = attr_map.get(key)
            if value:
                for candidate in value.split(","):
                    url = candidate.strip().split(" ", 1)[0]
                    if url:
                        self.urls.append(url)
        if tag in TEXT_TAG_BREAKS:
            self.parts.append("\n")
        for key in ("alt", "aria-label"):
            value = attr_map.get(key)
            if value:
                self.parts.append(value)

    def handle_startendtag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        self.handle_starttag(tag, attrs)
        if tag.casefold() in SKIP_TAGS and self.skip_depth:
            self.skip_depth -= 1

    def handle_endtag(self, tag: str) -> None:
        tag = tag.casefold()
        if tag in SKIP_TAGS:
            if self.skip_depth:
                self.skip_depth -= 1
            return
        if not self.skip_depth and tag in TEXT_TAG_BREAKS:
            self.parts.append("\n")

    def handle_data(self, data: str) -> None:
        if not self.skip_depth:
            self.parts.append(data)


def request(url: str, accept: str = "text/html,application/xhtml+xml") -> FetchResult:
    last_error = ""
    for attempt in range(RETRIES):
        started = time.monotonic()
        req = urllib.request.Request(
            url,
            headers={"User-Agent": USER_AGENT, "Accept": accept},
        )
        try:
            with urllib.request.urlopen(
                req,
                timeout=TIMEOUT_SECONDS,
                context=ssl.create_default_context(),
            ) as response:
                raw = response.read()
                content_type = response.headers.get_content_type()
                charset = response.headers.get_content_charset() or "utf-8"
                body = raw.decode(charset, "replace")
                return FetchResult(
                    url=url,
                    status=response.status,
                    final_url=response.geturl(),
                    content_type=content_type,
                    body=body,
                    error="",
                    elapsed_ms=round((time.monotonic() - started) * 1000),
                )
        except urllib.error.HTTPError as exc:
            try:
                raw = exc.read()
                body = raw.decode(exc.headers.get_content_charset() or "utf-8", "replace")
            except Exception:
                body = ""
            return FetchResult(
                url=url,
                status=exc.code,
                final_url=exc.geturl(),
                content_type=exc.headers.get_content_type() if exc.headers else "",
                body=body,
                error=f"HTTP {exc.code}",
                elapsed_ms=round((time.monotonic() - started) * 1000),
            )
        except Exception as exc:  # noqa: BLE001 - all network failures belong in the report
            last_error = f"{type(exc).__name__}: {exc}"
            if attempt + 1 < RETRIES:
                time.sleep(0.5 * (attempt + 1))
    return FetchResult(
        url=url,
        status=None,
        final_url=url,
        content_type="",
        body="",
        error=last_error,
        elapsed_ms=0,
    )


def fetch_json(url: str) -> tuple[Any, dict[str, str]]:
    result = request(url, "application/json")
    if result.status != 200:
        raise RuntimeError(f"Cannot fetch JSON {url}: {result.status} {result.error}")
    return json.loads(result.body), {}


def fetch_all_rest_items(origin: str, rest_base: str, fields: Iterable[str]) -> list[dict[str, Any]]:
    all_items: list[dict[str, Any]] = []
    page = 1
    field_arg = urllib.parse.quote(",".join(fields), safe=",")
    while True:
        url = f"{origin}/wp-json/wp/v2/{rest_base}?per_page=100&page={page}&_fields={field_arg}"
        result = request(url, "application/json")
        if result.status == 400 and page > 1:
            break
        if result.status != 200:
            raise RuntimeError(f"Cannot fetch {url}: {result.status} {result.error}")
        batch = json.loads(result.body)
        all_items.extend(batch)
        # The media endpoint on these sites occasionally returns short pages in
        # the middle of the result set (for example 87 items on page 15 while
        # page 16 still contains records). Stop only on an empty page/REST 400.
        if not batch:
            break
        page += 1
    return all_items


def decode_rendered(value: Any) -> str:
    if isinstance(value, dict):
        value = value.get("rendered", "")
    return html.unescape(str(value or "")).strip()


def replace_origin(url: str, origin: str) -> str:
    parsed = urllib.parse.urlsplit(url)
    path = parsed.path or "/"
    query = f"?{parsed.query}" if parsed.query else ""
    return f"{origin}{path}{query}"


def slice_frontend_content(document: str, environment: str) -> str:
    """Keep the page body while removing the old/new global header and footer."""
    if not document:
        return ""
    if environment == "prod":
        starts = [
            r'<div\s+class=["\']content(?:\s|["\'])',
            r'<div\s+class=["\'][^"\']*\bcontent\b[^"\']*["\']',
        ]
        end_pattern = r"<footer\b"
    else:
        starts = [
            r'<(?:main|section|div)\b[^>]*\bid=["\']main-content["\'][^>]*>',
            r'<div\b[^>]*\bclass=["\'][^"\']*\be-con-parent\b[^"\']*["\'][^>]*>',
        ]
        end_pattern = r'<footer\b[^>]*\bclass=["\'][^"\']*\bporumbesti-footer\b'

    start = None
    for pattern in starts:
        match = re.search(pattern, document, flags=re.IGNORECASE)
        if match:
            start = match.start()
            break
    if start is None:
        body_match = re.search(r"<body\b[^>]*>", document, flags=re.IGNORECASE)
        start = body_match.end() if body_match else 0
    end_match = re.search(end_pattern, document[start:], flags=re.IGNORECASE)
    end = start + end_match.start() if end_match else len(document)
    return document[start:end]


def parse_visible(fragment: str) -> tuple[str, list[str]]:
    parser = VisibleHTMLParser()
    try:
        parser.feed(fragment)
    except Exception:
        pass
    text = html.unescape(" ".join(parser.parts))
    text = unicodedata.normalize("NFKC", text)
    text = re.sub(r"\s+", " ", text).strip()
    return text, parser.urls


def parse_legacy_body(rendered_content: str) -> tuple[str, list[str]]:
    """Extract human content from REST HTML containing WPBakery shortcodes."""
    # The old REST endpoint exposes unexpanded [vc_*] wrappers. Their many
    # layout attributes are not visible page content and must not enter the
    # comparison.
    without_shortcodes = re.sub(r"\[(?:/?)[^\]]+\]", " ", rendered_content or "", flags=re.DOTALL)
    return parse_visible(without_shortcodes)


def normalize_text(value: str) -> str:
    value = unicodedata.normalize("NFKC", html.unescape(value or ""))
    value = value.casefold().replace("ş", "ș").replace("ţ", "ț")
    value = re.sub(r"https?://(?:dev\.)?comunaporumbesti\.ro", " comunaporumbesti ", value)
    value = re.sub(r"[^\w]+", " ", value, flags=re.UNICODE)
    return re.sub(r"\s+", " ", value).strip()


def token_counter(value: str) -> Counter[str]:
    return Counter(token for token in normalize_text(value).split() if token)


def overlap_scores(prod_text: str, dev_text: str) -> tuple[int, int, float, float]:
    prod_tokens = token_counter(prod_text)
    dev_tokens = token_counter(dev_text)
    prod_count = sum(prod_tokens.values())
    dev_count = sum(dev_tokens.values())
    overlap = sum((prod_tokens & dev_tokens).values())
    recall = overlap / prod_count if prod_count else 1.0
    precision = overlap / dev_count if dev_count else (1.0 if not prod_count else 0.0)
    return prod_count, dev_count, recall, precision


def clean_url(value: str) -> str:
    return html.unescape(value or "").replace("\\/", "/").strip(" \t\r\n\"'")


def asset_key(value: str) -> str | None:
    value = clean_url(value)
    if not value or value.startswith(("data:", "javascript:", "mailto:", "tel:", "#")):
        return None
    parsed = urllib.parse.urlsplit(value)
    filename = urllib.parse.unquote(parsed.path.rsplit("/", 1)[-1]).casefold()
    if not filename:
        return None
    suffix = Path(filename).suffix.casefold()
    if suffix not in ASSET_EXTENSIONS:
        return None
    if suffix in IMAGE_EXTENSIONS:
        stem = filename[: -len(suffix)]
        # Some old imports contain stacked generated-size suffixes, e.g.
        # photo-1024x768-300x225.jpg. Remove every trailing size marker.
        stem = re.sub(r"(?:-\d+x\d+)+$", "", stem)
        stem = re.sub(r"-scaled$", "", stem)
        filename = f"{stem}{suffix}"
    return filename


def asset_keys(urls: Iterable[str]) -> set[str]:
    return {key for key in (asset_key(url) for url in urls) if key}


def content_link_key(value: str) -> str | None:
    value = clean_url(value)
    if not value or value.startswith(("data:", "javascript:", "mailto:", "tel:", "#")):
        return None
    # Several legacy TablePress cells contain bare email addresses or a stray
    # ">" as href values. They are malformed contact links, not internal pages.
    if value == ">" or re.fullmatch(r"[^/\s@]+@[^/\s@]+\.[^/\s@]+", value):
        return None
    if asset_key(value):
        return None
    parsed = urllib.parse.urlsplit(urllib.parse.urljoin(PROD_ORIGIN, value))
    host = (parsed.hostname or "").casefold()
    if host not in {"primariaporumbesti.ro", "www.primariaporumbesti.ro", "dev.primariaporumbesti.ro"}:
        return None
    path = urllib.parse.unquote(parsed.path or "/").casefold()
    if path.startswith(("/wp-admin", "/wp-content", "/wp-json", "/author/", "/category/", "/tag/")):
        return None
    parts = [part for part in path.split("/") if part]
    if not parts:
        return "home"
    if parts[-1] in {"feed", "comments"}:
        return None
    # Production uses hierarchical page paths while the rebuilt site exposes
    # many matching records at a flat canonical path. The last slug is stable.
    return parts[-1]


def content_link_keys(urls: Iterable[str]) -> set[str]:
    return {key for key in (content_link_key(url) for url in urls) if key}


def title_in_text(title: str, text: str) -> bool:
    normalized_title = normalize_text(title)
    normalized_text = normalize_text(text)
    return not normalized_title or normalized_title in normalized_text


def classify(
    prod: FetchResult,
    dev: FetchResult,
    prod_token_count: int,
    recall: float,
    title_present: bool,
    missing_assets: set[str],
    dynamic_listing: bool,
    missing_links: set[str],
) -> tuple[str, str]:
    issues: list[str] = []
    severity = "ok"
    if prod.status != 200:
        issues.append(f"A publikus URL állapota: {prod.status or prod.error}")
        severity = "error"
    if dev.status != 200:
        issues.append(f"A fejlesztői URL állapota: {dev.status or dev.error}")
        severity = "critical"
    if not title_present:
        issues.append("A cím nem található a fejlesztői oldal látható tartalmában")
        severity = "error" if severity in {"ok", "warning"} else severity
    if dynamic_listing:
        if missing_links:
            issues.append(f"{len(missing_links)} publikus tartalmi hivatkozás nem látszik a listában")
            if len(missing_links) >= 20:
                severity = "critical"
            elif len(missing_links) >= 3:
                severity = "error"
            else:
                severity = "warning"
    elif prod_token_count >= 20:
        if recall < 0.20:
            issues.append(f"A publikus látható szöveg csak {recall:.1%}-ban azonosítható")
            severity = "critical"
        elif recall < 0.60:
            issues.append(f"A publikus látható szöveg csak {recall:.1%}-ban azonosítható")
            severity = "error" if severity in {"ok", "warning"} else severity
        elif recall < 0.85:
            issues.append(f"A publikus látható szöveg csak {recall:.1%}-ban azonosítható")
            severity = "error" if severity in {"ok", "warning"} else severity
        elif recall < 0.95:
            issues.append(f"Részleges szövegeltérés ({recall:.1%} megőrzött tartalom)")
            severity = "warning" if severity == "ok" else severity
    elif prod_token_count >= 8 and recall < 0.75:
        issues.append(f"Rövid tartalom részleges eltérése ({recall:.1%} megőrzött tartalom)")
        severity = "warning" if severity == "ok" else severity
    if missing_assets:
        issues.append(f"{len(missing_assets)} publikus dokumentum/kép hivatkozása nem látszik")
        severity = "error" if len(missing_assets) >= 3 and severity in {"ok", "warning"} else (
            "warning" if severity == "ok" else severity
        )
    return severity, "; ".join(issues) if issues else "Egyező/megőrzött tartalom"


def compare_item(
    item_type: str,
    prod_item: dict[str, Any],
    dev_item: dict[str, Any] | None,
    prod_result: FetchResult,
    dev_result: FetchResult,
) -> AuditRow:
    title = decode_rendered(prod_item.get("title"))
    prod_fragment = slice_frontend_content(prod_result.body, "prod")
    dev_fragment = slice_frontend_content(dev_result.body, "dev")
    prod_frontend_text, prod_frontend_urls = parse_visible(prod_fragment)
    dev_text, dev_urls = parse_visible(dev_fragment)
    legacy_text, legacy_urls = parse_legacy_body(str(prod_item.get("content", {}).get("rendered", "")))

    # For ordinary pages/posts the REST body is the cleanest representation of
    # editorial content. Archive/listing pages often consist only of a dynamic
    # shortcode; in that case compare the actually rendered front-end list.
    legacy_token_count = sum(token_counter(legacy_text).values())
    prod_frontend_token_count = sum(token_counter(prod_frontend_text).values())
    use_dynamic_frontend = item_type == "page" and legacy_token_count < 8 and prod_frontend_token_count >= 20
    prod_text = prod_frontend_text if use_dynamic_frontend else legacy_text
    prod_urls = prod_frontend_urls if use_dynamic_frontend else legacy_urls
    prod_tokens, dev_tokens, recall, precision = overlap_scores(prod_text, dev_text)

    prod_links = content_link_keys(prod_frontend_urls)
    dev_links = content_link_keys(dev_urls)
    missing_links = prod_links - dev_links
    dynamic_listing = item_type == "page" and len(prod_links) >= 3

    prod_assets = asset_keys(prod_urls)
    if not prod_assets:
        # Image/gallery shortcodes may reference attachment IDs rather than
        # URLs in REST. The rendered page exposes the actual file URLs.
        prod_assets = asset_keys(prod_frontend_urls)
    if dynamic_listing:
        # Card thumbnails are presentation, not missing editorial content. Keep
        # downloadable documents in scope.
        prod_assets = {key for key in prod_assets if Path(key).suffix.casefold() not in IMAGE_EXTENSIONS}
        dev_assets_for_compare = {
            key for key in asset_keys(dev_urls) if Path(key).suffix.casefold() not in IMAGE_EXTENSIONS
        }
    else:
        dev_assets_for_compare = asset_keys(dev_urls)
    dev_assets = asset_keys(dev_urls)
    missing_assets = prod_assets - dev_assets_for_compare
    title_present = title_in_text(title, dev_text) or str(prod_item.get("slug", "")) in {"home-ro", "home-hu"}
    severity, issue = classify(
        prod_result,
        dev_result,
        prod_tokens,
        recall,
        title_present,
        missing_assets,
        dynamic_listing,
        missing_links,
    )
    if dev_item is None:
        severity = "critical"
        issue = "A REST tartalomrekord hiányzik a fejlesztői oldalról; " + issue
    return AuditRow(
        item_type=item_type,
        item_id=int(prod_item["id"]),
        date=str(prod_item.get("date", "")),
        modified=str(prod_item.get("modified", "")),
        title=title,
        slug=str(prod_item.get("slug", "")),
        prod_url=prod_result.url,
        dev_url=dev_result.url,
        dev_canonical_url=str(dev_item.get("link", "")) if dev_item else "",
        prod_status=prod_result.status,
        dev_status=dev_result.status,
        prod_final_url=prod_result.final_url,
        dev_final_url=dev_result.final_url,
        prod_tokens=prod_tokens,
        dev_tokens=dev_tokens,
        content_recall=round(recall, 6),
        content_precision=round(precision, 6),
        prod_assets=len(prod_assets),
        dev_assets=len(dev_assets),
        missing_assets=" | ".join(sorted(missing_assets)),
        missing_asset_count=len(missing_assets),
        prod_links=len(prod_links),
        dev_links=len(dev_links),
        missing_links=" | ".join(sorted(missing_links)),
        missing_link_count=len(missing_links),
        dynamic_listing=dynamic_listing,
        title_present=title_present,
        severity=severity,
        issue=issue,
    )


def crawl_pairs(
    inventory: list[tuple[str, dict[str, Any], dict[str, Any] | None]],
) -> list[AuditRow]:
    work: dict[Any, tuple[str, dict[str, Any], dict[str, Any] | None, str]] = {}
    results: dict[tuple[str, int, str], FetchResult] = {}
    with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
        for item_type, prod_item, dev_item in inventory:
            prod_url = replace_origin(str(prod_item["link"]), PROD_ORIGIN)
            dev_url = replace_origin(str(prod_item["link"]), DEV_ORIGIN)
            for environment, url in (("prod", prod_url), ("dev", dev_url)):
                future = executor.submit(request, url)
                work[future] = (item_type, prod_item, dev_item, environment)

        total = len(work)
        completed = 0
        for future in as_completed(work):
            item_type, prod_item, _dev_item, environment = work[future]
            result = future.result()
            results[(item_type, int(prod_item["id"]), environment)] = result
            completed += 1
            if completed % 50 == 0 or completed == total:
                print(f"Front-end letöltés: {completed}/{total}", flush=True)

    rows: list[AuditRow] = []
    for item_type, prod_item, dev_item in inventory:
        item_id = int(prod_item["id"])
        rows.append(
            compare_item(
                item_type,
                prod_item,
                dev_item,
                results[(item_type, item_id, "prod")],
                results[(item_type, item_id, "dev")],
            )
        )
    return rows


def media_filename(item: dict[str, Any]) -> str:
    source = str(item.get("source_url", ""))
    return urllib.parse.unquote(urllib.parse.urlsplit(source).path.rsplit("/", 1)[-1])


def csv_safe_dict(row: AuditRow) -> dict[str, Any]:
    value = asdict(row)
    value["content_recall"] = f"{row.content_recall:.4f}"
    value["content_precision"] = f"{row.content_precision:.4f}"
    return value


def markdown_link(label: str, url: str) -> str:
    label = label.replace("[", "\\[").replace("]", "\\]")
    return f"[{label}]({url})"


def compact_title(value: str, limit: int = 90) -> str:
    value = re.sub(r"\s+", " ", value).strip()
    return value if len(value) <= limit else value[: limit - 1] + "…"


def write_reports(
    rows: list[AuditRow],
    inventories: dict[str, dict[str, list[dict[str, Any]]]],
    generated_at: str,
) -> None:
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
    csv_path = OUTPUT_DIR / "all-content-comparison.csv"
    json_path = OUTPUT_DIR / "all-content-comparison.json"
    md_path = OUTPUT_DIR / "summary-hu.md"

    with csv_path.open("w", encoding="utf-8-sig", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=list(asdict(rows[0]).keys()))
        writer.writeheader()
        writer.writerows(csv_safe_dict(row) for row in rows)

    payload = {
        "generated_at": generated_at,
        "production_origin": PROD_ORIGIN,
        "development_origin": DEV_ORIGIN,
        "rows": [asdict(row) for row in rows],
    }
    json_path.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")

    severity_order = {"critical": 0, "error": 1, "warning": 2, "ok": 3}
    flagged = sorted(
        (row for row in rows if row.severity != "ok"),
        key=lambda row: (severity_order[row.severity], row.content_recall, row.item_type, row.item_id),
    )
    counts = Counter(row.severity for row in rows)
    prod_by_type = {kind: {int(item["id"]): item for item in values} for kind, values in inventories["prod"].items()}
    dev_by_type = {kind: {int(item["id"]): item for item in values} for kind, values in inventories["dev"].items()}

    missing_records: list[tuple[str, dict[str, Any]]] = []
    dev_only_records: list[tuple[str, dict[str, Any]]] = []
    for item_type in ("page", "post"):
        prod_ids = prod_by_type[item_type]
        dev_ids = dev_by_type[item_type]
        missing_records.extend((item_type, prod_ids[item_id]) for item_id in sorted(prod_ids.keys() - dev_ids.keys()))
        dev_only_records.extend((item_type, dev_ids[item_id]) for item_id in sorted(dev_ids.keys() - prod_ids.keys()))

    prod_media = {int(item["id"]): item for item in inventories["prod"]["media"]}
    dev_media = {int(item["id"]): item for item in inventories["dev"]["media"]}
    missing_media = [prod_media[item_id] for item_id in sorted(prod_media.keys() - dev_media.keys())]
    dev_only_media = [dev_media[item_id] for item_id in sorted(dev_media.keys() - prod_media.keys())]

    title_differences: list[tuple[str, dict[str, Any], dict[str, Any]]] = []
    for item_type in ("page", "post"):
        for item_id in sorted(prod_by_type[item_type].keys() & dev_by_type[item_type].keys()):
            prod_item = prod_by_type[item_type][item_id]
            dev_item = dev_by_type[item_type][item_id]
            if normalize_text(decode_rendered(prod_item.get("title"))) != normalize_text(
                decode_rendered(dev_item.get("title"))
            ):
                title_differences.append((item_type, prod_item, dev_item))

    lines = [
        "# Comuna Porumbești – publikus és fejlesztői tartalmi audit",
        "",
        f"Ellenőrzés időpontja (UTC): **{generated_at}**",
        "",
        "## Összefoglaló",
        "",
        f"- Publikus: **{len(inventories['prod']['page'])} oldal**, **{len(inventories['prod']['post'])} bejegyzés**, **{len(inventories['prod']['media'])} médiaelem**.",
        f"- Fejlesztői: **{len(inventories['dev']['page'])} oldal**, **{len(inventories['dev']['post'])} bejegyzés**, **{len(inventories['dev']['media'])} médiaelem**.",
        f"- Ténylegesen megnyitott és összevetett front-end URL-párok: **{len(rows)}**.",
        f"- Automatikus minősítés: **{counts['ok']} rendben**, **{counts['warning']} figyelmeztetés**, **{counts['error']} hiba**, **{counts['critical']} kritikus**.",
        "",
        "A szöveges egyezés a publikus oldal ténylegesen látható tartalmának a fejlesztői oldalon megtalált arányát méri. A fejlécet, láblécet, JavaScriptet és CSS-t nem számítja bele. A képeknél a WordPress méretváltozatokat (például `-300x200`) azonos fájlnak tekinti.",
        "",
        "## Hiányzó tartalmi rekordok",
        "",
    ]

    if missing_records:
        for item_type, item in missing_records:
            lines.append(
                f"- **{item_type} #{item['id']}** — {markdown_link(compact_title(decode_rendered(item.get('title'))), str(item.get('link', '')))} ({item.get('date', '')})"
            )
    else:
        lines.append("- Nincs.")

    lines.extend(["", "## A fejlesztői oldalon csak ott létező tartalmi rekordok", ""])
    if dev_only_records:
        for item_type, item in dev_only_records:
            lines.append(
                f"- **{item_type} #{item['id']}** — {markdown_link(compact_title(decode_rendered(item.get('title'))), str(item.get('link', '')))}"
            )
    else:
        lines.append("- Nincs.")

    lines.extend(["", "## Hiányzó médiaelemek", ""])
    if missing_media:
        for item in missing_media:
            label = compact_title(decode_rendered(item.get("title")) or media_filename(item))
            lines.append(
                f"- **média #{item['id']}** — {markdown_link(label, str(item.get('source_url', '')))} (`{media_filename(item)}`)"
            )
    else:
        lines.append("- Nincs.")
    if dev_only_media:
        lines.extend(["", f"A fejlesztői oldalon ezen felül {len(dev_only_media)} csak ott létező médiaelem található."])

    lines.extend(["", "## Címeltérések azonos rekordazonosító mellett", ""])
    if title_differences:
        for item_type, prod_item, dev_item in title_differences:
            lines.append(
                f"- **{item_type} #{prod_item['id']}** — publikus: “{compact_title(decode_rendered(prod_item.get('title')))}”; fejlesztői: “{compact_title(decode_rendered(dev_item.get('title')))}”."
            )
    else:
        lines.append("- Nincs.")

    lines.extend(["", "## Tartalmi és elérési eltérések", ""])
    if flagged:
        lines.append("| Súlyosság | Típus / ID | Cím | Szöveg megőrzése | Hiányzó linkek | Hiányzó fájlok | Megállapítás |")
        lines.append("|---|---:|---|---:|---:|---:|---|")
        for row in flagged:
            title_link = markdown_link(compact_title(row.title, 65), row.dev_url)
            issue = row.issue.replace("|", "\\|")
            lines.append(
                f"| {row.severity} | {row.item_type} #{row.item_id} | {title_link} | {row.content_recall:.1%} | {row.missing_link_count} | {row.missing_asset_count} | {issue} |"
            )
    else:
        lines.append("- Nincs automatikusan észlelt eltérés.")

    lines.extend(
        [
            "",
            "## Fájlok",
            "",
            "- `all-content-comparison.csv`: minden ellenőrzött oldal/bejegyzés egy sorban; Excelben szűrhető.",
            "- `all-content-comparison.json`: a teljes géppel feldolgozható eredmény.",
            "- `summary-hu.md`: ez az összefoglaló.",
            "",
        ]
    )
    md_path.write_text("\n".join(lines), encoding="utf-8")


def main() -> int:
    generated_at = datetime.now(timezone.utc).replace(microsecond=0).isoformat()
    fields = ["id", "date", "modified", "slug", "link", "parent", "title", "content", "excerpt", "categories"]
    media_fields = ["id", "date", "modified", "slug", "source_url", "title", "media_type", "mime_type"]

    print("WordPress tartalomjegyzékek letöltése…", flush=True)
    inventories: dict[str, dict[str, list[dict[str, Any]]]] = {"prod": {}, "dev": {}}
    for environment, origin in (("prod", PROD_ORIGIN), ("dev", DEV_ORIGIN)):
        inventories[environment]["page"] = fetch_all_rest_items(origin, "pages", fields)
        inventories[environment]["post"] = fetch_all_rest_items(origin, "posts", fields)
        inventories[environment]["media"] = fetch_all_rest_items(origin, "media", media_fields)
        print(
            f"{environment}: {len(inventories[environment]['page'])} oldal, "
            f"{len(inventories[environment]['post'])} bejegyzés, "
            f"{len(inventories[environment]['media'])} médiaelem",
            flush=True,
        )

    inventory: list[tuple[str, dict[str, Any], dict[str, Any] | None]] = []
    for item_type in ("page", "post"):
        dev_items = {int(item["id"]): item for item in inventories["dev"][item_type]}
        for prod_item in inventories["prod"][item_type]:
            inventory.append((item_type, prod_item, dev_items.get(int(prod_item["id"]))))

    print(f"{len(inventory)} URL-pár front-end ellenőrzése…", flush=True)
    rows = crawl_pairs(inventory)
    rows.sort(key=lambda row: (row.item_type, row.date, row.item_id))
    write_reports(rows, inventories, generated_at)
    print(f"Kész: {OUTPUT_DIR}", flush=True)
    return 0


if __name__ == "__main__":
    sys.exit(main())
