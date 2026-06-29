# Changelog

All notable changes to this theme are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

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
