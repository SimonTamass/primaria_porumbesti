=== Primaria Porumbesti Elementor Widgets ===
Contributors: primariaporumbesti
Tags: elementor, municipality, multilingual, documents, accessibility
Requires at least: 6.4
Requires PHP: 8.0
Stable tag: 1.0.13
License: GPLv2 or later

A bilingual 24-widget Elementor system for the Porumbesti / Kokenyesd municipal portal.

== Description ==

Provides 24 purpose-built Elementor widgets, bilingual shared frontend templates, an optional document post type, a secure contact endpoint and a URL-preserving page rebuild tool.

Elementor Free is sufficient. Polylang integrations are used when Polylang is active; Elementor Pro is not required.

The rebuild tool:

* discovers existing Romanian and Hungarian pages without inventing public page slugs;
* preserves page IDs, titles, parents, slugs, permalinks and Polylang relations;
* keeps up to five restorable backups per page;
* rolls back automatically after permalink or Elementor rendering failures;
* preserves original text, media, galleries and supported legacy content.

The contact endpoint includes a nonce, honeypot, signed recipient validation, server-side field validation and one-minute throttling.

== Installation ==

1. Clone this repository directly into its final directory under wp-content/plugins using cPanel Git Version Control; do not add another directory around the repository root.
2. Pull plugin updates from the main branch in cPanel Git Version Control, then activate Elementor and this plugin.
3. Keep Polylang active before using the rebuild tool; rebuild actions stay disabled without the Romanian/Hungarian language relations.
4. Find the widgets in the Comuna Porumbesti Elementor category.
5. Use Tools > Comuna Porumbesti rebuild only on a backed-up local or staging copy.

== Changelog ==

= 1.0.13 =
* Removes the duplicated legacy source snapshot from both rebuilt homepages while retaining the restorable original-content backup.
* Adds a regression guard that keeps full legacy-content preservation on internal pages without appending it before either homepage footer.

= 1.0.12 =
* Replaces the unrelated legacy image placeholder with the Porumbești "P" monogram in news grids and archives.
* Removes obsolete cross-project references from the deployment documentation.

= 1.0.11 =
* Recovers the three legacy Visual Composer/TablePress department and leadership tables even when TablePress is not installed.
* Preserves the exact published Hungarian contact address and the original Romanian presentation-page title during rebuilds.

= 1.0.10 =
* Blocks every Elementor rebuild action while Polylang is inactive so Romanian/Hungarian identities and translation relations cannot be processed from an incomplete page inventory.
* Shows a bilingual administrator warning and keeps all rebuild buttons disabled until the language layer is available.

= 1.0.9 =
* Corrects the Romanian and Hungarian leadership-card URLs so the vice mayor and secretary open their existing role-specific pages.
* Documents the cPanel Git Version Control deployment layout used by the development site.

= 1.0.8 =
* Removes the separate language gateway so the Romanian homepage opens directly while the RO/HU switch remains available in the header.
* Simplifies the institutional monogram and favicon from CP to a single P.

= 1.0.7 =
* Preserves the complete original public content, tables, document links and media on every specialized rebuild page in addition to the redesigned sections.
* Restores the public webmail, Facebook, online portal, SGG transparency and official map links across page, archive, search and post templates.
* Corrects the official map position and removes an opening-hours value that was not present in the verified public source.

= 1.0.6 =
* Added a local full-content REST snapshot and a machine-checkable contract for every published page, post, category and referenced official document URL.
* Added page identity hashing, Polylang relation backups and automatic rollback when an ID, title, parent, slug, status, permalink or translation relation changes.

= 1.0.5 =
* Replaced the legacy wordmark with a compact institutional monogram across the static portal, Elementor header, footer, native WordPress fallback and favicon.

= 1.0.4 =
* Raised the visual system to a formal municipal standard with a readable institutional wordmark, disciplined accent hierarchy and refined leadership, document, CTA and footer treatments.
* Added bilingual homepage composition parity, clearer civic-status information and authentic Romanian-document labelling on Hungarian views.
* Improved WCAG contrast, focus visibility, mobile reflow, menu focus/scroll handling, form affordances and accessibility controls.

= 1.0.3 =
* Introduced a professional navy, burgundy, copper and blue-teal municipal palette with geometric header gradients.

= 1.0.2 =
* Introduced an interim graphite and gold municipal palette.

= 1.0.1 =
* Updated the shared static and Elementor palette to the current Primaria Porumbesti visual identity.
* Added cache-busting for the refreshed public styles.

= 1.0.0 =
* Initial bilingual release with 24 Elementor widgets.
* Added local fonts, responsive and accessible frontend assets.
* Added dynamic news, archive, single-post and document components.
* Added the optional document content type and secure contact endpoint.
* Added URL-preserving templates, five-version backups and automatic rollback.
