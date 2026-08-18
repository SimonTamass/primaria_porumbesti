#!/usr/bin/env python3
"""Capture the public Porumbesti WordPress content and URL contract locally.

The snapshot is read-only: it uses public REST endpoints and never authenticates
or writes to the live site. Full page/post content remains available for local
recovery, while the compact URL contract is suitable for permalink regression
tests before an Elementor rebuild.
"""

from __future__ import annotations

import argparse
import json
import re
import ssl
import time
import urllib.error
import urllib.parse
import urllib.request
from collections import Counter
from concurrent.futures import ThreadPoolExecutor, as_completed
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path
from typing import Any


ORIGIN = "https://primariaporumbesti.ro"
ROOT = Path(__file__).resolve().parents[1]
SNAPSHOT_PATH = ROOT / "content" / "live-content-snapshot.json"
CONTRACT_PATH = ROOT / "content" / "live-url-contract.json"
REPORT_PATH = ROOT / "QA-URL-REPORT.md"
USER_AGENT = "Mozilla/5.0 (compatible; PorumbestiLocalSnapshot/1.0)"
TIMEOUT = 45
REST_WORKERS = 10
CHECK_WORKERS = 12
DOCUMENT_EXTENSIONS = {
    ".csv", ".doc", ".docx", ".ods", ".odt", ".pdf", ".ppt", ".pptx",
    ".rar", ".rtf", ".txt", ".xls", ".xlsx", ".xml", ".zip",
}
IMAGE_EXTENSIONS = {".avif", ".gif", ".jpeg", ".jpg", ".png", ".svg", ".tif", ".tiff", ".webp"}


class LinkParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.urls: list[str] = []

    def handle_starttag(self, _tag: str, attrs: list[tuple[str, str | None]]) -> None:
        values = dict(attrs)
        for key in ("href", "src", "data-src", "data-lazy-src"):
            if values.get(key):
                self.urls.append(str(values[key]))
        for key in ("srcset", "data-srcset"):
            if values.get(key):
                self.urls.extend(part.strip().split(" ", 1)[0] for part in str(values[key]).split(",") if part.strip())


def request(url: str, method: str = "GET") -> tuple[bytes, Any, str, int]:
    parsed_url = urllib.parse.urlsplit(url)
    request_url = urllib.parse.urlunsplit(
        (
            parsed_url.scheme,
            parsed_url.netloc.encode("idna").decode("ascii"),
            urllib.parse.quote(parsed_url.path, safe="/%:@!$&'()*+,;=-._~"),
            urllib.parse.quote(parsed_url.query, safe="=&%:@!$'()*+,;/?-._~"),
            "",
        )
    )
    last_error = ""
    for attempt in range(3):
        request_object = urllib.request.Request(
            request_url,
            method=method,
            headers={"User-Agent": USER_AGENT, "Accept": "application/json,text/html;q=0.9,*/*;q=0.8"},
        )
        try:
            with urllib.request.urlopen(request_object, timeout=TIMEOUT, context=ssl.create_default_context()) as response:
                return response.read() if method != "HEAD" else b"", response.headers, response.geturl(), int(response.status)
        except urllib.error.HTTPError as error:
            if method == "HEAD" and error.code in {400, 403, 405, 501}:
                return request(url, "GET")
            last_error = f"HTTP {error.code}"
        except Exception as error:  # noqa: BLE001 - recorded in the local audit.
            last_error = str(error)
        time.sleep(0.5 * (attempt + 1))
    raise RuntimeError(f"Unable to fetch {url}: {last_error}")


def rest_page(endpoint: str, fields: str, page: int) -> tuple[list[dict[str, Any]], Any]:
    query = urllib.parse.urlencode({"per_page": 100, "page": page, "orderby": "id", "order": "asc", "_fields": fields})
    raw, headers, _final_url, _status = request(f"{ORIGIN}/wp-json/wp/v2/{endpoint}?{query}")
    return json.loads(raw.decode("utf-8")), headers


def fetch_endpoint(endpoint: str, fields: str, allow_filtered: bool = False) -> tuple[list[dict[str, Any]], int]:
    first, headers = rest_page(endpoint, fields, 1)
    expected_total = int(headers.get("X-WP-Total", str(len(first))))
    total_pages = int(headers.get("X-WP-TotalPages", "1"))
    pages: dict[int, list[dict[str, Any]]] = {1: first}
    if total_pages > 1:
        with ThreadPoolExecutor(max_workers=REST_WORKERS) as executor:
            futures = {executor.submit(rest_page, endpoint, fields, page): page for page in range(2, total_pages + 1)}
            for future in as_completed(futures):
                page = futures[future]
                pages[page] = future.result()[0]
    items = [item for page in sorted(pages) for item in pages[page]]
    if len(items) != expected_total:
        pages = {page: rest_page(endpoint, fields, page)[0] for page in range(1, total_pages + 1)}
        items = [item for page in sorted(pages) for item in pages[page]]
    if len(items) != expected_total and not allow_filtered:
        raise RuntimeError(f"Incomplete {endpoint} inventory: expected {expected_total}, received {len(items)}")
    return sorted(items, key=lambda item: int(item.get("id", 0))), expected_total


def rendered(value: Any) -> str:
    return str(value.get("rendered", "")) if isinstance(value, dict) else str(value or "")


def language_from_url(url: str) -> str:
    path = urllib.parse.urlsplit(url).path
    if path.startswith("/ro/"):
        return "ro"
    if path.startswith("/hu/"):
        return "hu"
    return "und"


def normalize_url(value: str, base_url: str) -> str:
    value = value.strip()
    if not value or value.startswith(("#", "mailto:", "tel:", "javascript:", "data:")):
        return ""
    absolute = urllib.parse.urljoin(base_url, value)
    parsed = urllib.parse.urlsplit(absolute)
    if parsed.scheme not in {"http", "https"}:
        return ""
    host = parsed.netloc.casefold()
    if host == "www.primariaporumbesti.ro":
        host = "primariaporumbesti.ro"
    path = re.sub(r"/{2,}", "/", parsed.path or "/")
    return urllib.parse.urlunsplit((parsed.scheme.casefold(), host, path, parsed.query, ""))


def content_urls(items: list[dict[str, Any]]) -> set[str]:
    found: set[str] = set()
    for item in items:
        base_url = str(item.get("link", ORIGIN + "/"))
        parser = LinkParser()
        parser.feed(rendered(item.get("content")))
        parser.feed(rendered(item.get("excerpt")))
        for value in parser.urls:
            normalized = normalize_url(value, base_url)
            if normalized:
                found.add(normalized)
    return found


def public_route(kind: str, item: dict[str, Any]) -> dict[str, Any]:
    url = str(item.get("link", ""))
    translations = item.get("translations", {})
    return {
        "kind": kind,
        "id": int(item.get("id", 0)),
        "slug": str(item.get("slug", "")),
        "language": str(item.get("lang") or language_from_url(url)),
        "url": url,
        "parent": int(item.get("parent", 0)),
        "modified": str(item.get("modified", "")),
        "title": rendered(item.get("title")) if kind != "category" else str(item.get("name", "")),
        "translations": translations if isinstance(translations, (dict, list)) else {},
    }


def frontend_links(url: str) -> list[str]:
    raw, _headers, final_url, _status = request(url)
    parser = LinkParser()
    parser.feed(raw.decode("utf-8", errors="replace"))
    return sorted({normalized for value in parser.urls if (normalized := normalize_url(value, final_url))})


def local_coverage(routes: list[dict[str, Any]], post_count: int, category_count: int) -> dict[str, Any]:
    source_manifest = json.loads((ROOT / "content" / "source-manifest.json").read_text(encoding="utf-8"))
    static_views = [
        {"language": str(record.get("language", "")), "url": str(record.get("source", "")), "local": str(record.get("local", ""))}
        for record in source_manifest.get("sourceRecords", [])
        if str(record.get("local", "")).endswith(".html") and str(record.get("source", "")).startswith(ORIGIN + "/")
    ]
    static_source_urls = {view["url"] for view in static_views}
    generic_pages = [route for route in routes if route["kind"] == "page" and route["url"] not in static_source_urls]
    return {
        "staticViewCount": len(static_views),
        "staticSourceUrlCount": len(static_source_urls),
        "staticViews": static_views,
        "genericPageCount": len(generic_pages),
        "genericPages": generic_pages,
        "dynamicPostCount": post_count,
        "dynamicCategoryCount": category_count,
        "mediaPolicy": "Existing WordPress upload URLs remain unchanged; metadata is snapshotted locally and visual assets used by the 14 static views are stored under assets/images.",
    }


def check_one(url: str) -> dict[str, Any]:
    started = time.monotonic()
    try:
        _body, _headers, final_url, status = request(url, "HEAD")
        return {"url": url, "status": status, "finalUrl": final_url, "elapsedMs": round((time.monotonic() - started) * 1000), "error": ""}
    except Exception as error:  # noqa: BLE001 - recorded in the local audit.
        return {"url": url, "status": None, "finalUrl": "", "elapsedMs": round((time.monotonic() - started) * 1000), "error": str(error)}


def check_urls(urls: list[str]) -> list[dict[str, Any]]:
    results: dict[str, dict[str, Any]] = {}
    with ThreadPoolExecutor(max_workers=CHECK_WORKERS) as executor:
        futures = {executor.submit(check_one, url): url for url in urls}
        for future in as_completed(futures):
            results[futures[future]] = future.result()
    return [results[url] for url in urls]


def comparable_url(url: str) -> str:
    parsed = urllib.parse.urlsplit(url)
    return urllib.parse.urlunsplit((parsed.scheme.casefold(), parsed.netloc.casefold(), urllib.parse.unquote(parsed.path), urllib.parse.unquote(parsed.query), ""))


def write_report(snapshot: dict[str, Any], contract: dict[str, Any]) -> None:
    checks = contract.get("checks", [])
    statuses = Counter(str(item.get("status") or "error") for item in checks)
    broken = [item for item in checks if item.get("status") not in {200, 301, 302, 303, 307, 308}]
    redirected = [item for item in checks if item.get("finalUrl") and comparable_url(str(item["finalUrl"])) != comparable_url(str(item["url"]))]
    counts = snapshot["counts"]
    coverage = contract["localCoverage"]
    lines = [
        "# Élő tartalom- és URL-leltár",
        "",
        f"Rögzítés: {snapshot['capturedAt']}",
        "",
        "## Tartalom",
        "",
        f"- {counts['pages']} publikus oldal",
        f"- {counts['posts']} publikus bejegyzés",
        f"- {counts['categories']} kategória",
        f"- {counts['media']} nyilvánosan visszaadott médiarekord a REST által jelzett {counts['mediaReported']} rekordból ({counts['images']} kép, {counts['documents']} dokumentumfájl; {counts['mediaUnavailable']} rekordot a publikus API szerveroldali szűrése nem ad vissza)",
        f"- {counts['contentLinks']} egyedi hivatkozás az oldal- és bejegyzéstartalmakban",
        "",
        "## URL-szerződés",
        "",
        f"- {len(contract['publicRoutes'])} WordPress-útvonal rögzítve ID-val és sluggal.",
        f"- {len(contract['documentUrls'])} tartalomból hivatkozott dokumentum-URL rögzítve.",
        f"- {coverage['staticViewCount']} kiemelt statikus nézet {coverage['staticSourceUrlCount']} egyedi élő forrásútvonalhoz.",
        f"- {coverage['genericPageCount']} további oldal a meglévő WordPress ID-n működő általános Elementor-sablonnal.",
        f"- {coverage['dynamicPostCount']} bejegyzés és {coverage['dynamicCategoryCount']} kategória a közös dinamikus frontend-sablonokkal.",
        f"- Állapotok: {', '.join(f'{key}: {value}' for key, value in sorted(statuses.items())) if checks else 'nem futott hálózati állapotellenőrzés'}.",
        f"- Átirányított URL-ek: {len(redirected)}.",
        f"- Hibás vagy nem elérhető URL-ek: {len(broken)}.",
        "",
        "A helyi Elementor-átépítés az útvonalakat nem hozza létre újra: a meglévő WordPress ID-khez ír kizárólag Elementor metaadatot, ezért a fenti slugok és permalinkek változatlanul megőrzendők.",
    ]
    if broken:
        lines.extend(["", "## Az élő oldalon már hibás URL-ek", ""])
        lines.extend(f"- `{item['url']}` — {item.get('status') or item.get('error')}" for item in broken[:100])
    REPORT_PATH.write_text("\n".join(lines) + "\n", encoding="utf-8")


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--check-status", action="store_true", help="Check public routes and referenced document URLs without changing the site.")
    parser.add_argument("--refresh-local-coverage", action="store_true", help="Refresh local coverage in the existing snapshot contract without network access.")
    args = parser.parse_args()
    if args.refresh_local_coverage:
        snapshot = json.loads(SNAPSHOT_PATH.read_text(encoding="utf-8"))
        contract = json.loads(CONTRACT_PATH.read_text(encoding="utf-8"))
        contract["localCoverage"] = local_coverage(contract["publicRoutes"], len(snapshot["posts"]), len(snapshot["categories"]))
        CONTRACT_PATH.write_text(json.dumps(contract, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
        write_report(snapshot, contract)
        print(f"Local coverage refreshed: {contract['localCoverage']['staticViewCount']} static views, {contract['localCoverage']['genericPageCount']} generic pages")
        return 0
    captured_at = datetime.now(timezone.utc).replace(microsecond=0).isoformat()

    pages, _pages_reported = fetch_endpoint("pages", "id,date,modified,slug,status,type,link,parent,menu_order,title,content,excerpt,featured_media,lang,translations")
    posts, _posts_reported = fetch_endpoint("posts", "id,date,modified,slug,status,type,link,title,content,excerpt,featured_media,categories,tags,lang,translations")
    categories, _categories_reported = fetch_endpoint("categories", "id,slug,link,name,parent,count,lang,translations")
    media, media_reported = fetch_endpoint("media", "id,date,modified,slug,status,link,parent,title,caption,alt_text,media_type,mime_type,source_url", allow_filtered=True)

    all_content_urls = content_urls(pages + posts)
    internal_urls = sorted(url for url in all_content_urls if urllib.parse.urlsplit(url).netloc.casefold() in {"primariaporumbesti.ro", "www.primariaporumbesti.ro"})
    document_urls = sorted(url for url in internal_urls if Path(urllib.parse.urlsplit(url).path).suffix.casefold() in DOCUMENT_EXTENSIONS)
    image_urls = sorted(url for url in internal_urls if Path(urllib.parse.urlsplit(url).path).suffix.casefold() in IMAGE_EXTENSIONS)
    routes = [public_route("page", item) for item in pages] + [public_route("post", item) for item in posts] + [public_route("category", item) for item in categories]
    routes.sort(key=lambda item: (item["kind"], item["url"]))
    frontend_home_urls = {
        "ro": frontend_links(f"{ORIGIN}/ro/prima/"),
        "hu": frontend_links(f"{ORIGIN}/hu/fooldal/"),
    }
    media_mime = Counter(str(item.get("mime_type", "unknown")) for item in media)
    media_documents = sum(count for mime, count in media_mime.items() if mime and not mime.startswith("image/"))

    snapshot = {
        "schemaVersion": 1,
        "sourceOrigin": ORIGIN,
        "capturedAt": captured_at,
        "counts": {
            "pages": len(pages),
            "posts": len(posts),
            "categories": len(categories),
            "media": len(media),
            "mediaReported": media_reported,
            "mediaUnavailable": max( 0, media_reported - len(media) ),
            "images": sum(count for mime, count in media_mime.items() if mime.startswith("image/")),
            "documents": media_documents,
            "contentLinks": len(all_content_urls),
            "internalContentLinks": len(internal_urls),
            "documentLinks": len(document_urls),
            "imageLinks": len(image_urls),
        },
        "pages": pages,
        "posts": posts,
        "categories": categories,
        "media": media,
        "mediaByMime": dict(sorted(media_mime.items())),
    }
    check_targets = sorted({item["url"] for item in routes} | set(document_urls))
    contract = {
        "schemaVersion": 1,
        "sourceOrigin": ORIGIN,
        "capturedAt": captured_at,
        "policy": {
            "preserveIds": True,
            "preserveSlugs": True,
            "preserveParents": True,
            "preservePermalinks": True,
            "preservePolylangRelations": True,
            "documentsRemainOnSourceUrls": True,
        },
        "publicRoutes": routes,
        "documentUrls": document_urls,
        "internalContentUrls": internal_urls,
        "frontendHomeUrls": frontend_home_urls,
        "localCoverage": local_coverage(routes, len(posts), len(categories)),
        "checks": check_urls(check_targets) if args.check_status else [],
    }
    SNAPSHOT_PATH.write_text(json.dumps(snapshot, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    CONTRACT_PATH.write_text(json.dumps(contract, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    write_report(snapshot, contract)
    print(json.dumps(snapshot["counts"], ensure_ascii=False))
    print(f"URL routes: {len(routes)}; status checks: {len(contract['checks'])}")
    print(f"Snapshot: {SNAPSHOT_PATH}")
    print(f"Contract: {CONTRACT_PATH}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
