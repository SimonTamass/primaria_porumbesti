# Primăria Porumbești Elementor Widgets

This repository is the root directory of the installable WordPress plugin,
matching the deployment layout used by `comuna_agris`. Clone the repository
directly into a directory under `wp-content/plugins/`; do not place another
plugin folder around it.

## Installation

1. Clone `https://github.com/SimonTamass/primaria_porumbesti.git` directly to a
   plugin directory on the development WordPress installation.
2. Activate **Primăria Porumbești Elementor Widgets** beside Elementor.
3. Elementor Pro is not required. Keep Polylang active to preserve the exact
   Romanian/Hungarian relationships of the cloned website.

## Included

- 24 editable Elementor widgets in `includes/widgets/`;
- separate Romanian and Hungarian rebuild flows;
- existing-page and Polylang-aware route resolution;
- URL, page-identity, rendering, backup, and rollback protection;
- frontend archive, search, post, and document templates;
- production CSS, JavaScript, local fonts, and plugin images;
- plugin smoke tests and the live development-site audit tool.

The plugin rebuilds the existing cloned WordPress pages. The database content,
page IDs, documents, and media library remain in the cloned database and
`wp-content/uploads`; they are not duplicated inside this code repository.

Requirements: WordPress 6.4+, PHP 8.0+, Elementor, and optionally Polylang.
