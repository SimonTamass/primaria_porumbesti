# Primaria Porumbesti Elementor plugin

This repository publishes the rebuild as one self-contained WordPress plugin.

The installable plugin source is located at:

```text
wordpress-plugin/primaria-porumbesti-elementor/
```

Do not copy the repository root into WordPress. Copy only that plugin directory
to `wp-content/plugins/`, or create a ZIP whose top-level directory is
`primaria-porumbesti-elementor` and upload it in WordPress.

The plugin contains:

- 24 custom Elementor widgets under `includes/widgets/`;
- the safe page rebuild tool under `includes/class-template-applier.php`;
- frontend templates, styles, scripts, local fonts, and plugin assets;
- automatic page backup and rollback when a permalink, identity, or Elementor
  render check fails.

Requirements: WordPress 6.4+, PHP 8.0+, and Elementor. Elementor Pro is not
required. Polylang is supported when present.
