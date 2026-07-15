# Muuttohaukat WordPress Theme

Custom WordPress theme for [Muuttohaukat](https://muuttohaukat.com), rebuilt from the legacy Haukka theme.

**Version:** 1.0.12  
**Repository:** [github.com/Tapiokansleri/Muuttohaukat-wordpress-theme](https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme)

## Requirements

- WordPress 6.0+
- PHP 7.4+
- [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/) (ACF JSON field groups are bundled)
- Recommended: Beaver Builder, Polylang (Finnish content)

## Installation

1. Download `Muuttohaukat.zip` from the [latest release](https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/latest).
2. In WordPress admin go to **Appearance → Themes → Add New → Upload Theme**.
3. Upload the ZIP and activate **Muuttohaukat**.

Or clone into your themes directory:

```bash
git clone https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme.git wp-content/themes/Muuttohaukat
```

## Updates

The theme includes a built-in GitHub updater. When a new [GitHub release](https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases) is published with a version tag higher than the installed theme, WordPress will show an update under **Dashboard → Updates** and **Appearance → Themes**.

Updates are fetched from this repository automatically — no separate update plugin is required.

### Dynamics 365 (quote forms)

Without FTP access, set the Azure Function URL in **Appearance → Teeman asetukset → Muut asetukset → D365 endpoint URL**.

Alternatively, add to `wp-config.php` (overrides the admin setting):

```php
define('MUUTTOHAUKAT_D365_ENDPOINT', 'https://func-muuttohaukat-xrm-prod.azurewebsites.net/api/AddOfferToDynamics?id=...&code=...');
```

Without either, LibreForm submissions save but are not forwarded to Dynamics 365.

## Security

This is a **public** repository. Do **not** commit:

- Azure Function `code` keys or full D365 endpoint URLs with query parameters
- `wp-config.php`, `.env`, or database dumps
- API keys, passwords, or private tokens

Store the full D365 endpoint in **WordPress admin** (Teeman asetukset) or in `wp-config.php` on the server only. GitHub push protection blocks known Azure keys from entering this repo.

The theme source only contains the public Azure hostname and API path — never authentication secrets.

## Theme features

- Gutenberg + ACF block-based page building
- Landing page template with custom blocks
- Post listing block with card grid
- Header customizer (CTA buttons, colours)
- Footer widget areas with ACF menu fallback
- Beaver Builder custom modules (FAQ, button)
- Finnish-first translation setup

## Page templates

| Template | File |
|----------|------|
| Default | `singular.php` |
| Contained page | `template-contained-page.php` |
| Landing page | `template-landing-page.php` |

## Development

Theme bootstrap lives in `functions.php`. Includes are loaded from `inc/`, blocks from `blocks/`, and ACF field groups from `acf-json/`.

CSS is split across `assets/css/` (`00-tokens.css`, `02-base.css`, `header.css`, `content.css`, etc.) and enqueued from `inc/enqueue.php`.

### Releasing a new version

1. Bump `Version` in `style.css`.
2. Commit and push to `main`.
3. Create and push a tag (example for 1.1.0):

```bash
git tag v1.1.0
git push origin v1.1.0
```

The GitHub Actions workflow builds `Muuttohaukat.zip` and attaches it to the release. Sites running an older version will pick up the update automatically.

**Important:** Install from **Releases → Muuttohaukat.zip**, not the green "Code → Download ZIP" button on the repo page (that produces a `Muuttohaukat-wordpress-theme-main` folder).

## License

GPL v2 or later — see [style.css](style.css).

## Author

[Kansleri](https://kansleri.fi) / [Tapio Kauranen](https://tapiokauranen.com)
