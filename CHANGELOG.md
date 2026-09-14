# Changelog

All notable changes to this theme are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [1.2.8] - 2026-09-14

### Changed

- Gravity Forms front end matches the LibreForm quote forms: 48px square inputs, regular-weight labels, section titles like LibreForm headings, checkbox toggle switches, round radios, yellow uppercase buttons and a black progress bar (Orbital theme kept via `gform_default_styles` + `assets/css/gravity-forms.css`)

### Fixed

- DaisyUI primary yellow (LibreForm submit buttons) is exactly `#ffed00` (hue 56 rendered `#ffee00`)

## [1.2.7] - 2026-09-14

### Added

- `[muuttopaivat]` shortcode: three-month popularity calendar (current month first) with weekday headers. Outputs only the calendar — no heading, padding or width cap; months go side by side when the container is at least 56rem wide. Attributes: `color` (hex, default `#FFED00`), `link` (bookable days go to `link?date=Y-m-d`). Past dates are gray and not clickable.
- Quote forms prefill **Muuttopäivä** (`Muuttopvm`) from `?date=Y-m-d`; on the `/tarjouspyynto/` chooser the date is carried to the form pages below it
- Gravity Forms Dynamics feed: **Lisätiedot template** with the merge tag picker composes `Lisätiedot` from fields Dynamics has no key for; lines whose fields are all empty are left out, and an empty template keeps the existing mapping

### Changed

- Footer uses the black/white colour tokens: pure black background, token-based text colours and a divider above the bottom bar

### Fixed

- Brand yellow is `#ffed00` everywhere: yellow buttons (Muuttohaukat-painike), header/content CTA fallbacks, pricing-table headers and the logo swoosh were `#f3e200` / `#ffee00`

## [1.2.6] - 2026-09-07

### Added

- Footer E-E-A-T credentials strip (1992, 35+ years, 100 000+ moves, 4 offices, 100+ staff) and claim as one of Finland’s largest home and business movers

## [1.2.5] - 2026-08-26

### Fixed

- Dynamics entry payload is a selectable/scrollable `<pre>` (bare textareas were unusable in admin)
- **Lähetä uudelleen** permission check uses Gravity Forms' `current_user_can_any` (incl. `gform_full_access`) and registers `admin-post` handlers outside the add-on init path

## [1.2.4] - 2026-08-26

### Fixed

- Dynamics HTTP 400 from Azure when `historyId` is JSON `null` (Azure expects `System.Int32`) — GF bridge and LibreForm forwarder now send `0`
- Decode HTML entities in GF source URLs (`&amp;` → `&`) before building the Dynamics payload
- Surface Azure response body on the entry Dynamics box for failed sends

## [1.2.3] - 2026-08-26

### Added

- Show the stored Dynamics payload JSON on the Gravity Forms entry Dynamics box (helps debug Azure HTTP 400s)

## [1.2.2] - 2026-08-26

### Fixed

- Stop logging LibreForm honeypot rejections ("Captcha wasn't filled properly") to the PHP/form log — expected bot noise, not lost leads

## [1.2.1] - 2026-08-26

### Added

- Gravity Forms → Dynamics bridge embedded in the theme (`inc/gf-dynamics*`): same Azure payload as LibreForm, feed mapping UI, sidebar **Dynamics** page with connection test, and entry resend. Boots on `init` so it works from a theme (not only as a plugin). LibreForm forwarding unchanged

### Fixed

- Floating CTA banner lifts above `.site-footer` on scroll so it no longer covers the footer

## [1.2.0] - 2026-08-20

### Added

- **Head-koodi** tab in Teeman asetukset for site verification tags (Bing Webmaster Tools, Pinterest and similar). Accepts `meta`, `link` and external `script` tags; inline JavaScript is stripped, and snippets are sanitized both on save and on output

## [1.1.9] - 2026-08-20

### Fixed

- Mobile menu no longer reveals page content at the bottom: page scroll is locked while the overlay is open and the overlay covers the visual viewport (`100dvh` + safe area)

### Added

- Log the real reason a LibreForm submission fails to the form log, instead of LibreForm silently swallowing it (only an `Undefined variable $useFallback` warning was left behind)
- Regenerate missing Beaver Builder layout cache files before BB enqueues them, stopping `file_get_contents(...cache/NNNN-layout.css)` warnings and pages rendering without layout CSS

## [1.1.8] - 2026-08-18

### Fixed

- Stop LibreForm DB errors during daily auto-draft cleanup when a form has no submissions table yet

## [1.1.7] - 2026-08-10

### Added

- Henkilöstö bulk-import: example CSV download, **Tuo ja korvaa** submit that replaces persons and saves
- Language flags (FI default) shown beside each name; CSV photo column (media ID or URL)

### Changed

- Henkilöstö typography tweaks for role/focus lines

## [1.1.6] - 2026-08-10

### Added

- Beaver Builder module **Henkilöstö**: section heading + repeatable staff entries (photo left, details right), mobile-optimized 3-column grid

## [1.1.5] - 2026-08-08

### Fixed

- Stop fatal errors when theme PHP files are hit directly without WordPress bootstrap (`Muuttohaukat\app()` undefined) by exiting early unless `ABSPATH` is defined
- Prevent form POSTs from blocking 10–30 s on Dynamics: D365 forwarding is now non-blocking
- Capture the real `wp_mail` failure reason for confirmation emails, and wrap submission side-effects so they cannot break LibreForm

[1.2.2]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.2.2
[1.2.1]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.2.1
[1.2.0]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.2.0
[1.1.9]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.1.9
[1.1.8]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.1.8
[1.1.7]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.1.7
[1.1.6]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.1.6
[1.1.5]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.1.5

## [1.1.4] - 2026-07-27

### Changed

- Do not write successful D365 form forwarding to the PHP error log (failures are still logged)

[1.1.4]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.1.4

## [1.1.3] - 2026-07-20

### Added

- Per-block background control (white, yellow, black) on all landing section blocks
- Shared `haukka.png` hero default image and `--content-max-width: 1350px` layout token
- D365 forwarding retry on transient network errors and clearer error codes in logs

### Changed

- Remove landing page template; landing blocks work via editor sidebar/pattern on any page
- Rename landing blocks to generic titles and update default hero/service copy
- Final CTA section uses selectable background instead of fixed black/yellow styling
- Widen landing inner containers to 1350px

### Fixed

- Fix Gutenberg validation errors when inserting landing blocks (icon-item/buttons markup)
- Fix production fatal error in `index.php` (unused `get_queried_object()` call)
- Tighten hero trust badge spacing; center final CTA buttons
- White text on black landing section backgrounds

[1.1.3]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.1.3

## [1.1.2] - 2026-07-15

### Fixed

- Restore PostListing card eagle images by using bundled theme SVGs instead of broken media-library srcset paths
- Skip responsive srcset output for SVG attachments with corrupt WordPress metadata
- Hide Cookiebot floating privacy trigger on mobile so it no longer covers the menu hamburger

[1.1.2]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.1.2

## [1.1.1] - 2026-07-15

### Added

- Font Awesome icon picker block (`muuttohaukat/icon-item`) with visual picker in the Gutenberg editor
- Editor-only fonts and padding for Muuttohaukat blocks
- D365 endpoint backup storage and automatic self-heal from backup or `wp-config.php`

### Changed

- Rename Gutenberg block category from `Landing:` to `Muuttohaukat`
- Align hero, floating CTA, and button blocks with the Beaver Builder `mh-painike` button styles
- Compact floating CTA sizing on mobile while keeping buttons side by side
- Shorter mobile header bar with a larger logo

### Fixed

- Stop D365 endpoint from being wiped when saving other Teeman asetukset tabs (split settings groups per tab)
- Restore full-width frontend layouts by removing global `body` padding from `theme.json`
- Fix unsupported `muuttohaukat/icon-item` block registration in the editor
- Fix header CTA hover shrinking and Font Awesome loading conflicts with Beaver Builder

### Security

- Block accidental clearing of a working D365 endpoint during unrelated settings saves

[1.1.1]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.1.1

## [1.1.0] - 2026-07-15

### Added

- Disable comments throughout the frontend, REST API, XML-RPC, and WordPress admin
- Add administrator/editor SVG uploads with bundled upload-time sanitization
- Replace Breadcrumb NavXT with accessible theme-native visual breadcrumbs

### Changed

- Move the active WPCodeBox pricing-table and home-hero styles into conditional theme assets
- Transfer Google Search Console verification to The SEO Framework without storing the value in theme source
- Deactivate the seven redundant utility plugins on theme activation while retaining their files and data for rollback
- Gate plugin deactivation on replacement readiness and package Composer dependencies in release ZIPs

### Security

- Reject unsafe SVG markup and remote SVG references before files enter the media library
- Limit SVG size, remove external CSS/assets, and require an allowed role with upload capability
- Keep the broad public WP Query REST endpoint disabled in favor of the validated PostListing endpoint

[1.1.0]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.1.0

## [1.0.14] - 2026-07-15

### Added

- Seed empty footer widget areas with editable legacy footer links on theme activation

### Fixed

- Automatically deactivate standalone Kansleri Hellobar and Floating CTA plugins on theme activation
- Keep header CTA dimensions stable on hover and increase the hover chevron size

[1.0.14]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.14

## [1.0.13] - 2026-07-15

### Security

- Validate the complete D365 Azure Function URL and mask its `code` value in admin
- Require explicit code re-entry when the D365 destination or account id changes
- Verify D365 HTTP responses and log only redacted status information
- Remove anonymous form debug destinations and harden public REST endpoints with validation and rate limits
- Replace unsafe form message HTML rendering and harden media-library SVG sanitization
- Restrict the PHP log viewer to redacted D365/theme-form entries

### Added

- Restore Google Tag Manager, global Leadoo, and the `acf/leadoo` block from Haukka
- One-time non-destructive migration for Haukka header, logo, navigation, and CTA theme mods
- Add missing `muuttovinkit` and `muuttoauto` ACF post-type definitions
- Add a token-aligned `theme.json` color palette

### Fixed

- Make GitHub updater folder migration transactional and avoid activating an inactive theme
- Preserve Haukka as a rollback theme and remove only a successfully replaced active legacy folder
- Support legacy PostListing template names and safely handle REST failures
- Replace `wp_reset_query()` with `wp_reset_postdata()`
- Remove duplicate landing inserter registration and full-page Hellobar output buffering
- Replace broken/staging landing defaults with packaged theme assets and production-relative links
- Restore Breadcrumb NavXT output in the footer
- Gracefully degrade when ACF Pro is unavailable
- Guard content preview generation against missing posts and paragraphs

[1.0.13]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.13

## [1.0.12] - 2026-07-15

### Security

- Removed `id` and `code` query parameters from the default D365 endpoint in theme source
- Documented secret-handling rules in README (public repo)

[1.0.12]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.12

## [1.0.11] - 2026-07-15

### Changed

- D365 endpoint input is pre-filled with the default Haukka/Azure Function URL when no saved value exists

[1.0.11]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.11

## [1.0.10] - 2026-07-15

### Changed

- D365 endpoint URL is always editable in admin; saved value overrides `wp-config.php`

### Added

- **Muut asetukset → Error log**: D365 form submission log (stored in DB) and PHP error log tail for troubleshooting

[1.0.10]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.10

## [1.0.9] - 2026-07-15

### Added

- **Appearance → Teeman asetukset → Muut asetukset**: D365 endpoint URL field — configure Dynamics 365 forwarding from WordPress admin without FTP or `wp-config.php` access

[1.0.9]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.9

## [1.0.8] - 2026-07-15

### Fixed

- LibreForm → Dynamics 365 forwarding: `inc/forms.php` reads `MUUTTOHAUKAT_D365_ENDPOINT` from `wp-config.php` (GitHub push protection blocks storing the Azure key in theme source)
- GitHub theme updater now uses canonical folder slug `Muuttohaukat` instead of binding to `get_template()` (fixes `Muuttohaukat-wordpress-theme-main` installs)
- One-time migration renames legacy theme folders (`Muuttohaukat-wordpress-theme-main`, `muuttohaukat`) to `Muuttohaukat` on theme load
- Release ZIP renamed to `Muuttohaukat.zip` with inner folder `Muuttohaukat/`
- Landing block default images use the canonical `Muuttohaukat` theme folder

[1.0.8]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.8

## [1.0.6] - 2026-06-29

### Fixed

- Montserrat fonts and theme images: `client.css` now uses relative `../fonts/` and `../img/` URLs instead of hardcoded `/wp-content/themes/muuttohaukat/` paths
- GitHub theme updater flattens nested release folders (e.g. repo `-main` subfolders) and no longer falls back to GitHub zipballs
- Release workflow verifies font files are included in `muuttohaukat.zip`
- Reverted BB Heading module padding hack that misaligned titles (v1.0.5)

[1.0.6]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.6

## [1.0.5] - 2026-06-29

### Fixed

- Beaver Builder page headings no longer use a global `margin: 0 0 20px` reset that misaligned titles with padded content modules
- BB Heading modules get matching horizontal padding; personnel section titles center above staff grids

[1.0.5]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.5

## [1.0.4] - 2026-06-29

### Fixed

- Button hover chevron: label stays centered at rest; on hover it shifts left and the chevron appears on the right without changing button width
- Shared chevron styles in `assets/css/03-button-chevron.css` (header CTAs, Gutenberg buttons, landing, Beaver Builder painike)

[1.0.4]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.4

## [1.0.3] - 2026-06-29

### Fixed

- GitHub theme updater now injects update info when WordPress reads the update transient (not only when saving it), so custom themes show updates even when `wp_update_themes()` skips the check
- Updater no longer requires the theme to already appear in WordPress.org's checked list
- GitHub API requests include a proper `User-Agent`; release cache clears on update admin screens
- **Teeman asetukset → Muut asetukset**: manual "Tarkista päivitykset nyt" button to force a check

[1.0.3]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.3

## [1.0.1] - 2026-06-29

### Added

- **Teeman asetukset** admin page under Appearance (floating CTA, hellobar, links to other theme settings)

### Changed

- Moved Floating CTA and Hellobar settings from Settings menu into the unified theme settings page

[1.0.1]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0.1

## [1.0] - 2026-06-29

### Added

- Initial public release of the Muuttohaukat theme (refactored from Haukka)
- GitHub-based theme updater (`inc/ThemeUpdater.php`)
- Contained page template
- Gutenberg button styling aligned with header CTAs
- Post listing grid layout (2 cols mobile, 3 cols desktop)
- Footer ACF menu fallback for widget columns
- Landing page blocks and templates
- Beaver Builder modules (FAQ, custom button)

### Changed

- Theme-wide yellow/black button palette
- Header secondary CTA default colour to black
- Mobile menu current item styling for readability

[1.0]: https://github.com/Tapiokansleri/Muuttohaukat-wordpress-theme/releases/tag/v1.0
