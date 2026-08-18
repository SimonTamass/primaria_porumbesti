# Porumbești / Kökényesd municipal portal

This repository contains a bilingual local prototype and an installable Elementor plugin for the Porumbești municipal website. It is designed for local review only; nothing in this project deploys to or changes the live website.

## Included deliverables

- 14 static views: 7 Romanian pages in `ro/` and 7 Hungarian pages in `hu/`.
- Local Manrope and Source Sans 3 webfonts, optimized source imagery, shared CSS and JavaScript.
- A source manifest at `content/source-manifest.json`, including live source URLs, local media paths and usage.
- A complete public REST snapshot at `content/live-content-snapshot.json` and a machine-checkable URL baseline at `content/live-url-contract.json`.
- 24 Elementor widgets under the `PrimariaPorumbesti` namespace and `primaria-porumbesti` text domain.
- Dynamic posts, categories and language-aware queries, plus the optional `porumbesti_document` post type.
- A secure `porumbesti_contact` endpoint with nonce, honeypot, recipient integrity validation, field validation and one-minute throttling.
- A URL-preserving template applier with at most five restorable backups per page and automatic rollback after permalink or Elementor rendering failures.
- Shared frontend templates for archives, search results and single posts/documents.
- Full original-content retention on redesigned pages, including tables, document links, legacy media and otherwise unplaced images.
- Preserved public webmail, Facebook, citizen portal, SGG transparency and official map links from the existing website.

Elementor Free is sufficient. Polylang is supported when present; Elementor Pro is not required.

## Live-content and URL baseline

Refresh the read-only local inventory with:

```powershell
python tools/snapshot-live-content.py --check-status
```

The command stores all publicly returned page/post content and media metadata locally, records every page/post/category ID, slug and permalink, and checks the current public routes plus referenced official document URLs. The baseline refreshed on 17 August 2026 contains 40 pages, 533 posts, 34 categories, 633 official document URLs and 18,545 unique content links. It never authenticates or writes to the live site. WordPress uploads stay on their existing URLs; the rebuild changes Elementor metadata on existing page IDs instead of recreating routes.

## Local prototype

From this directory:

```powershell
npm run build
python -m http.server 8776
```

Open `http://127.0.0.1:8776/`. The root URL renders the Romanian homepage directly; Hungarian remains available through the RO/HU switch in the header.

Run the static checks with:

```powershell
npm run test:prototype
```

The test verifies all 14 views, local images/fonts, language attributes, one H1 per page, skip links and internal links.

## Plugin

Upload `output/primaria-porumbesti-elementor.zip` through **Plugins → Add New → Upload Plugin**, then activate it beside Elementor. The widgets appear in the **Comuna Porumbești** Elementor category.

The document post type is enabled by default. It can be disabled before registration without changing the plugin:

```php
add_filter( 'porumbesti_enable_document_type', '__return_false' );
```

The rebuild interface is under **Tools → Comuna Porumbești rebuild**. It resolves existing pages and Polylang translations rather than creating guessed replacement pages. Every page is backed up before writing Elementor metadata. The tool preserves the post ID, title, parent, slug, language relation and permalink, then validates the permalink and rendered Elementor output. Specialized redesigns also render a complete preserved copy of the original public content so biographies, declaration links, tables, media and legacy document references are not lost. A failure restores the previous state automatically.

Do not run rebuild actions against a live installation until a separate deployment phase has been approved and a full database/files backup exists.

## Tests

PHP smoke tests can be run from the project root:

```powershell
Get-ChildItem tests -Filter *.php | ForEach-Object { php $_.FullName }
```

Requirements: WordPress 6.4+, PHP 8.0+, Elementor, and optionally Polylang.
