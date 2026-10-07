# Changelog

All notable changes to this theme are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [1.2.10] - 2026-10-07

### Added

- Landing-prices block: `multiplier` attribute ("Hintakerroin" in the editor) derives another price list from the same rows in `price_rows()`; every price is multiplied and rounded to the nearest 5 €. The full-service move page /kotimuutto/tayden-palvelun-muutto/ uses 2.15 (Tapio 7.10.2026: the full-service price is 115 % more than the move, including packing and unpacking). Other pages keep 1.

## [1.2.9] - 2026-10-06

### Added

- Landing-kantoapu block: `mh-landing-split__media--badge` shows a logo or round emblem at badge size (max 420px) instead of the full column width (the Vattenfall picture on /ymparisto/)

- Landing-kantoapu block: `mh-landing-split--top` for two text columns that start at the top, and `.mh-video-link` for a video still that opens the video (front page "Miksi Muuttohaukat?")

- The site runs without Beaver Builder: everything is edited in Gutenberg (Tapio, 22.9.2026). Checked on all 344 sitemap and archive URLs with Beaver Builder and Beaver Themer deactivated: same status, H1, staff, image and form counts as with them on, no errors, no Beaver Builder markup left
- `inc/Staff.php`: staff are person posts (Henkilöt in the admin) grouped by the new Henkilöryhmä taxonomy (Pääkaupunkiseutu, Hyvinkää, Tampere, Turku, Koko Suomi, Johto HR), ordered by page order, with photo as featured image, the ACF "Person" fields and a `mh_kielet` languages field (flags). `staff_persons()` and `staff_render()` print the same markup as the old Beaver Builder Henkilöstö module (`partials/staff-list.php`, `StaffHelpers`)
- Henkilöstö block (`blocks/henkilosto/`, `muuttohaukat/henkilosto`): a group or named people, one to three columns, server-side preview in the editor
- Archive heading in `index.php` (category, tag, author, month): eyebrow, one H1 with the term, author or month and the term or author description, on the same left edge as the cards. The author archive used to come from Themer layout 8421 and the other archives had no H1

- Styles for the renewed Gutenberg pages (all former Beaver Builder and old ACF wrapper/prose pages, converted by `work/wp_scripts/wpx_modernize.php`): `.mh-text-sec` (heading and intro left, text right, 100px apart; single column without a heading; wide variant for reviews, calendars and directories), `.mh-features` (h3 + text in two columns), article cards built from core blocks, and `core/details` for FAQ items without borders
- Shortcodes for the converted pages: `[artikkelit maara="…"]` (latest articles as cards, for the old post grids), `[henkilosto sivu="…" solmu="…"]` (a Henkilöstö module kept in a page's Beaver Builder data), `[toimipisteet]` (the four offices with map links) and `[yhteyshenkilot nimet="…"]` (named people from the staff templates; replaces the ACF Personnel block and its outdated person posts)
- `.mh-quote`: the Tarjouspyyntö block #3398 in its renewed markup (offer left, business moves and TilaaMuutto.fi right, stacked buttons, reviews below)
- `[nostot sivut="ID:kuva:otsikko,…" sarakkeet="2|3|4" kuvat="ei"]` (`inc/ArticleCards.php`, `page_card_html()`): highlights of other pages as article cards, text from the page's search description; used on /kotimuutto/ for the moving packages, extra services and guides. Card sections on landing pages keep one 20px rhythm between eyebrow, heading and intro and 40px before the cards
- `inc/Offices.php`: `[toimipiste_tiedot toimipiste="…"]` (address with a map link, opening hours and phone; without an office the switchboard, e-mail and hours) for the hero of the office pages and /yhteystiedot/, `[henkilosto pohja="…"]` (people from any Beaver Builder staff template, e.g. Koko Suomi and Johto HR), `office_map_html()` / `office_map_link()` and `staff_settings()`, which `office_staff()` now uses
- landing-local block: `map` attribute shows an embedded Google map and a route link under the office card; the block may be used several times on a page (the contact page has one per office), and it leaves out the link to the area page on the area page itself
- `inc/CityDirectory.php`: `[paikkakunnat palvelu="muutto|muuttosiivous|muuttolaatikot"]`, one locality directory for every service (all published city pages grouped by the serving office, office from the moving pages' `_mh_city` / `_mh_office`, Finnish sort order, current page marked, optional button; group headings link to the office area pages for moving). Used on the front page (was four region links), `/muuttosiivous/` (was a post grid with 13 H2s), the 13 cleaning city pages (was the landing-nearby chips) and, through `[muuttolaatikot_paikkakunnat]`, the box hub
- `inc/ArticleCards.php`: one article card for every article feed (photo, date, title, excerpt; three per row, two on tablets, one on phones; no borders, backgrounds or shadows). Articles without a featured image get one of twelve brand photos, handed out in date order so neighbouring cards differ (`article_fallback_images()`)
- City landing pages: the Paikallinen palvelu section shows the serving office's people from the Beaver Builder staff templates (`office_staff_html()` in `inc/LandingLocal.php`, also on the `/muutto/` pages)
- `inc/LandingThemer.php`: posts whose content holds landing blocks skip the Beaver Builder Themer singular layout, so rebuilt `/muutto/` and `/muuttosiivous/` pages render like the city pages
- `inc/StructuredData.php`: MovingCompany provider, Service markup on six service pages, Offer rows on the price page (from the same price rows as the price table) and FAQPage from the FAQ page's own questions (audit 3.8); company name, business ID and the four offices on the contact page and as one line in the footer
- `inc/Robots.php`: robots.txt rules through The SEO Framework (`the_seo_framework_robots_txt`), replacing the Virtual Robots.txt plugin, incl. `Disallow: /*?date=` (audit 3.3)
- `inc/Rewrites.php`: `/muuttotarvikkeet/page/N/` paginates the news listing instead of hitting the `accessory` post type and 404ing
- `inc/BoxCities.php`: `[muuttolaatikot_paikkakunnat]` lists every published moving box city page grouped by the serving office, plus region pages (audit 3.6)
- Google reviews on landing pages come from the serving office's collection (Hyvinkää, Vantaa, Tampere or Turku) instead of Turku everywhere (audit 3.9)
- `inc/CleaningCrossSell.php`: every moving landing page offers muuttosiivous right after its prices, linking to the city's own cleaning page (13 cities) or to `/muuttosiivous/`
- `inc/CompanyPage.php`: `[muuttohaukat_yritystiedot]` for the new `/yritystiedot/` page (facts, offices, agreements, services) and its MovingCompany markup with founding date, founder, business ID and the four offices (audit action 10); `company_facts()` holds the verified facts
- `inc/BoxSupplies.php`: `[muuttolaatikot_tarvikkeet]` shows the cardboard boxes and packing supplies for sale on the moving box page (audit action 3)
- Box city page sections modelled on the box hub `/muuttolaatikot-ja-muuttotarvikkeet/` (`inc/BoxSupplies.php`): `[muuttolaatikot_vuokra]` (rented boxes and trolleys as photo cards with day prices), `[muuttolaatikot_toimitus]` (delivery days, time windows and delivery prices by distance, plus the drive from the serving office when `place`, `office` and `km` are given) and `[muuttolaatikot_pakkausohje]` (numbered packing advice). The data sits in `box_rental_products()`, `box_delivery()` and `box_packing_tips()`; all sections share the two-column `.mh-box-sec` frame
- Article templates (`inc/Articles.php`, `assets/css/article.css`): posts no longer use Themer layout 8255. `single-post.php` is the default text article (date, reading time, title, excerpt, featured image beside the title, readable column); the template "Artikkeli: lohkot ja palstat" (`template-article-blocks.php`) lays the content out like a page for blocks, columns and landing blocks, leaves the header out when the content has its own H1, and is used automatically by Beaver Builder articles. Both end with the author box (BB template 8437), a quote call to action and "Lue myös", and print Article markup
- `[yritysmuutto_yhteyshenkilot]` (`inc/LandingLocal.php`): the people responsible for business moves, office by office, from the Beaver Builder staff templates (audit action 7)
- Yritysmuutto quote form: "Työpisteiden määrä" field (`template-parts/FormBusinessMove.php`); `inc/forms.php` moves it into the first line of Lisätiedot, so WPLF's field list, its submissions table and the Dynamics function need no change

### Changed

- Form pages (`wpx_form_pages.php`: /kokemus/, /laheltapiti/, /rekry/tyohakemus/, the three /tarjouspyynto/ forms, /ilmoituskanava/, /henkilotietojen-tieto-ja-poistopyynnot/): no mascot, and the heading and form in one centred 760px column (`mh-landing-hero--form` in blocks/landing-hero/style.css); the stray photo above the hero on /kokemus/ and /rekry/tyohakemus/ is gone, and the data request form moved into the hero with the site's yellow send button

- /kotimuutto/muuttopalvelun-hinta/ ("Hinnasto", `wpx_hinnasto.php`): the page's texts kept and laid out as hero with price figures, the price table from `price_rows()` (the landing-prices block, replacing the page's own table with its "450–450 €" row), price guide, the four packages as photo cards linking to their pages, what the price includes, the five price factors as numbered steps, the 2026 price on black, extra services, saving tips, why Muuttohaukat with the video, and the quote request
- Numbered steps (`.mh-landing-steps`, also /yritysmuutto/): big numbers instead of top lines, body-size text, 22px step headings, space above a button after the grid
- Item headings in `.mh-features` and in a second split text column are 22px instead of the 30px section H3
- Price table on phones: headings may wrap and the padding is tighter, so the three columns fit without sideways scrolling

- Cleaning pages finished (Tapio 22.9.2026: "Muuttosiivous sivut on todella karun näköinen"): the 13 city pages (`wpx_siivous.php content`) and /muuttosiivous/ (`wpx_siivous_hub.php`, own texts kept) got a photo section with what the cleaning covers, the price with three figures (`.mh-price-facts`), the serving office's Google reviews, and a closing quote section; the hub also the company story on black and the other services as cards. The trust fact "30+ v" became "1992"
- /kokemuksia/ (`wpx_kokemuksia.php`): the stray photo above the hero is the hero photo (`.mh-landing-hero__media--photo`), the hero links to each office, and each office's Google reviews run in three columns (`.mh-reviews-wall`, about 8 600px instead of 16 000px) with black names and the yellow review button
- An eyebrow + H2 group inside a text section head (`.mh-text-sec__head .mh-landing-sec-head`) joins the 20px rhythm instead of adding a 50px margin and half width
- Core landing buttons on yellow and black sections: the colour swap now beats the default button rule, so the "Soita" button is white on black instead of invisible

- /usein-kysyttyja-kysymyksia/ (`work/wp_scripts/wpx_ukk.php`): the 20 questions grouped into four topics with the questions as open/close items (`.mh-faq` in content.css: round yellow +/–, black on yellow sections), topic links in the hero, the home move coordinators of each office, the author pattern and a quote call to action; answers unchanged
- `faq_schema()`: on a page with open/close items only the `summary` texts count as questions, so topic headings that end with a question mark are not added to FAQPage
- The author box styles (`.mh-author`) moved from article.css to content.css, so the synced pattern also looks right on pages

- Finish for the split sections (landing-kantoapu; front page, /kotimuutto/ and eight other pages): the text column has one 20px rhythm (eyebrow, heading, paragraphs, lists and buttons spaced by the flex gap only), a subheading straight under the H2 reads as a 22px lead instead of a second big heading, later H3s get 40px above them, and photos (jpg) share a 4:3 crop so a portrait photo no longer makes its section 1000px tall; PNG illustrations keep their shape
- Google review widget (`.rpi`): the theme font instead of Arial, black names, and yellow square-cornered buttons without shadows instead of blue rounded ones

- Landing-local block (office pages, /yhteystiedot/, city, cleaning and box pages): the office's people sit in the left column under the text instead of below both columns, two per row from 1280px and one per row between 901 and 1279px; on phones they follow the text and come before the office card. Standalone people lists (Henkilöstö block, `[henkilosto]`) keep their spacing. Running-text link underlines leave the people lists and the office card alone

- Landing-cases block (references on /yritysmuutto/ and /julkisen-sektorin-muutot/): no borders or boxes, two columns 100px apart, one 20px rhythm between year, name and text, and a bold "Kaikki referenssit" link 48px below
- Landing-final-cta block: laid out like the hero instead of centred: heading and text on the left, buttons on the right, stacking under 1024px. The core button wrapper (`.wp-block-button.mh-landing__button`) no longer gets the link's 20px 50px padding a second time, which made the two buttons wrap onto separate rows
- Links inside running text on landing pages (`.mh-landing p > a` without a class) are underlined; they were plain black text

- Staff lists read person posts instead of the Beaver Builder staff templates: `[henkilosto]`, `[yhteyshenkilot]`, `[toimipisteet]`, `[yritysmuutto_yhteyshenkilot]`, the landing-local office card and the office pages. `staff_settings()` maps the old template slugs to groups (`staff_group_for_slug()`)
- The article author box is the synced pattern "Kirjoittaja: Totte Vivolin" (`wp_block` `kirjoittaja-totte-vivolin`, edited under Patterns) instead of Beaver Builder template 8437
- `faq_schema()` reads Beaver Builder data only while a page is still built with it, and otherwise the Gutenberg content, headings and `details` summaries included (20 questions on the FAQ page)
- The button and staff stylesheets moved from `fl-builder/modules/…/css/` to `assets/css/painike.css` and `assets/css/henkilosto.css`; the `fl-builder/` folder is only loaded by Beaver Builder itself and can go once the plugin is removed

- One paragraph size in the whole theme: `--content-font-size` follows `--body-font-size` (18px, 13px on phones), and Gutenberg content, card excerpts (`.mh-article-card__text`, `.mh-box-card__text`, `.mh-box-item__desc`), packing tips, the price note, the #3398 texts, the office hours and area links no longer set their own 14 to 17px sizes. Paragraphs and lists with the Gutenberg presets normal/medium/large (large was 36px) and Beaver Builder text modules with their own per-breakpoint sizes follow the body size too; headings keep theirs
- Mobile header: 64px bar (was 56px) with a 120px logo (was 150px) so it has room above and below; the menu close cross is a 48px touch target with a 44px glyph, like the hamburger
- Office card in the landing-local block has no border or box
- The Tarjouspyyntö block #3398 has 140px of space below it (was 88px) before the Google reviews
- Nearby towns ("Muutot myös lähikuntiin", "Muuttolaatikot myös lähikuntiin"; the landing-nearby block and `inc/Nearby.php`) use the locality directory layout instead of pill-shaped links: heading, text and the area page button on the left, "Lähimmät paikkakunnat" in four columns on the right (`nearby_directory_html()` in `inc/CityDirectory.php`, which replaces `nearby_body_html()`)
- The PostListing block's Card template (the "Ajankohtaista Muuttohaukoilla" reusable block on /ajankohtaista/ and 16 other pages, and its REST pagination) renders the shared article card instead of the DaisyUI shadow card with a yellow eagle and a "Lue lisää" button; the block heading is left-aligned at content width
- "Lue myös" under articles uses the same article cards, now with photos
- Locality directory CSS classes renamed from `.mh-box-cities*` to `.mh-cities*`
- The legacy Tarjouspyyntö block (#3398) on landing pages is laid out like the hero: offer on the left, the two other options as cards on the right, no centred text, `.mh-painike` buttons
- Paikallinen palvelu section: 100px column gap and one even vertical rhythm between eyebrow, heading and text
- Calendar section heading names the city ("Varaa muuttopäivä Lahdessa …") and the section is printed only on moving landing pages, no longer on every subpage
- `[muuttolaatikot_paikkakunnat]` restyled like the hero: eyebrow, heading and intro on the left (sticky on desktop, one 20px gap between them), the offices on the right with their towns in four even columns (two on phones); the margin resets now beat the Beaver Builder heading and paragraph margins that had pushed the eyebrow, heading and intro 32 to 40px apart
- `[muuttolaatikot_tarvikkeet]` restyled: intro and order button on the left, the products on the right in two columns with a product photo beside each name (the hub's photos where an accessory has no featured image, `box_supply_images()`), no borders, backgrounds or shadows
- Rental prices follow the box hub: Datakaappi 9,61 €/kpl/pv replaces the ATK-laatikko 1,70 € row that came from the old `/muuttolaatikot/` page; `box_rental_prices()` now derives from `box_rental_products()`
- Footer: no borders or divider lines (widget titles, list rows, the credentials strip and its dividers, the claim line and the bottom bar)
- Site header: no box shadow under the bar; default logo maximum width 150px (was 200px) and header height 100px (was 80px), so the logo and buttons have more room above and below; the current top-level menu item is no longer yellow (default active colour `#1a1a1a`, like the other items). All three stay Customizer settings
- Landing nearby block accepts up to 40 links (default still 15), for the office area pages that list their whole area
- `[muuttolaatikot_hinnat]` (`inc/BoxSupplies.php`): the box rental price table from `box_rental_prices()`, used on the moving box city pages, which are now built from landing blocks and no longer use Themer layout 6357
- The moving box hub is `/muuttolaatikot-ja-muuttotarvikkeet/` again (the page in the live menu), not `/muuttolaatikot/`: the company page link list (`inc/CompanyPage.php`) and the Service entry in `structured_data_pages()` point there

### Removed

- The empty "Muuttopalvelut" post type (ACF JSON `post_type_6a01b8fe39c62.json`) that showed under Landing pages in the admin menu
- Footer: the "35+ vuotta historiaa" fact is gone (the company was founded in 1992), "1992" and the company name link to `/yritystiedot/`, and the contact page links there too

### Fixed

- The front page template (`front-page.php`) hands a front page built from landing blocks to `singular.php`, so it gets the `.mh-landing` wrapper; without it the hero had no spacing between its paragraphs and the landing section styles did not apply

- Pages with the "Contained page" template and landing blocks (/julkisen-sektorin-muutot/, /referenssit/, /kokemuksia/, /tarjouspyynto/, /tarjouspyynto/kotimuutto/ and the five /yritysmuutto/ subpages) render in the `.mh-landing` wrapper like every other landing page (`template-contained-page.php` hands them to `singular.php`); in the container their text sections and features lost their layout

- `box_supply_summary()` skips the hero eyebrow, a price-only line and shortcodes, so the supplies list no longer starts every product with "Muuttotarvikkeet" after the product pages moved to Gutenberg
- Article pages scrolled sideways on phones when the title was one long word (ENERGIANSÄÄSTÖVIIKKO): the hero title and card titles may now break inside words
- City pages printed a second Google review grid below the footer: the per-office swap of `[brb_collection]` (`inc/LandingLocal.php`) also caught the plugin's site-wide flash pop-up (option `brb_glob_colls`, printed in `wp_footer`) and turned it into the office grid. The swap now skips `wp_footer` and the site-wide collections
- ACF PRO 6.8 wrapped every ACF block's inner blocks in `div.acf-innerblocks-container` on the front end, which broke the flex and grid layouts of `acf/wrapper` and `acf/prose` (the Tarjouspyyntö block #3398 fell back to one centred column). `inc/ACF.php` turns the wrapper off with `acf/blocks/wrap_frontend_innerblocks`, and the #3398 rules in `landing.css` accept both structures; its two right-hand options no longer have a border or background
- Calendar day links carry `rel="nofollow"` (with robots.txt `Disallow: /*?date=`), audit 3.2
- Internal links that hit redirects: header Tarjouspyyntö button `/tarjouspyynto/`, footer `/sivukartta/`, form privacy and supplies links, and the old Personnel block office links (`/hyvinkaa-uusimaa/`, closed offices to `/yhteystiedot/`)
- Image alt texts (audit 3.7): `Media\image()` falls back to a given text or a meaningful attachment title (file and camera names are skipped); post cards use the post title and the Personnel block the person's name; `wp_content_img_tag` fills a hard-coded `alt=""` in content and Beaver Builder text modules from the attachment's alt text, trusting a `wp-image-N` class only when it points at the same file

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
