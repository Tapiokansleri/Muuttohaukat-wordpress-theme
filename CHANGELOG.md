# Changelog

All notable changes to this theme are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

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
- Landing block default images use `/wp-content/themes/Muuttohaukat/img/` paths

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
