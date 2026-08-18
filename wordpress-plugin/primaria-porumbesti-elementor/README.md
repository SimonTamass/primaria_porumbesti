# Primaria Porumbesti Elementor Widgets

Self-contained WordPress plugin for rebuilding the Porumbesti / Kokenyesd
municipal website from 24 editable Elementor widgets.

## Installation

Copy this entire `primaria-porumbesti-elementor` directory to
`wp-content/plugins/`, or ZIP this directory and upload it through
**Plugins → Add New → Upload Plugin**. Activate Elementor first, then activate
this plugin. Elementor Pro is not required.

The widgets appear in the **Comuna Porumbesti** Elementor category. The
URL-preserving page rebuild interface is available under
**Tools → Comuna Porumbesti rebuild**.

## Included plugin code

- `includes/widgets/`: 24 Elementor widget classes;
- `includes/class-widget-registry.php`: Elementor widget registration;
- `includes/class-template-applier.php`: bilingual page rebuild definitions,
  backups, permalink checks, identity checks, and automatic rollback;
- `includes/class-frontend-templates.php`: archive, search, post, and document
  rendering;
- `assets/`: production CSS, JavaScript, local fonts, and plugin imagery;
- `templates/`: plugin frontend template.

Requirements: WordPress 6.4+, PHP 8.0+, and Elementor. Polylang is supported
when installed.
