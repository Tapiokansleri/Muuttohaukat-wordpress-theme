# Changelog

All notable changes to this theme are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [1.2.38] - 2026-10-30

### Changed

- Layout v2 for the whole page (Tapio 30.10.2026: "Poista eyebrowt", "Joka toinen osio tulee olla keltainen", "Korjaa välityksiä, fonttikokoja jne jotta sivusto on miellyttävä lukea"):
  - no eyebrows (`.mh-landing-kicker`, `.mh-cities__kicker`);
  - the same air around every section, 56px on phones and about 100px on wide screens;
  - line height 1.65 on wide screens and text lines of about 70 characters at most;
  - an H3 right under the H2 reads as the section's lead;
  - the buttons sit a little apart from the text;
  - one button style on the page: sentence case with an arrow after the text, as in the two choices (the floating buttons in sentence case too, with a shadow so they show on yellow sections);
  - the calendar's month names are the size of the other H3s and left-aligned, the months are white cards on a yellow section, and phones show two months, without the current one when fewer than eight bookable days are left in it.
- The choice cards use the page's text size, and the card title is 24px at most.

## [1.2.37] - 2026-10-30

### Changed

- The two-choice section in the brand colours instead of the dark colours of the client's mockup (Tapio 30.10.2026: "Käytä muuttohaukkojen brändivärejä. Musta ei ole brändiväri"): a yellow section (landing-why with the yellow background and `mh-choices-section`), white cards without borders or stripes, the line icon on a yellow chip, notes and checks in the text colours. The order button stays yellow on the white card (on a yellow section the theme turns it white), and the quote button is black as everywhere on the site.

## [1.2.36] - 2026-10-30

### Changed

- The two-choice section (`.mh-choices`, `.mh-choice-card`) follows the client's mockup (Tapio 30.10.2026: "Luo boksit näin miten asiakas halusi"): a dark section (`mh-choices-section` on a landing-why block with the black background), dark cards with a thin border and a yellow (price) or light (expert help) stripe on top, a line icon, full-width buttons in sentence case with an arrow that always shows, a note under each button and three checks under the cards (`.mh-choices__checks`).

### Removed

- The light card look of 1.2.32 and the example price, phone and business line styles of 1.2.33–1.2.34 (`.mh-choice-card__prices`, `.mh-choice-card__phone`, `.mh-choices__business`), which the mockup does not have. `[hintahaarukka]` stays.

## [1.2.35] - 2026-10-30

### Changed

- Layout v2: the floating buttons stay hidden while the hero's own buttons are in view and fade in once they have scrolled away. They offer the same two actions, and on a small phone (375×667) and a laptop (1366×768) they covered the hero's second button. Without JavaScript they show as before.

## [1.2.34] - 2026-10-30

### Changed

- Layout v2 headings on phones: 32/28/22px for a landing page's H1, H2 and H3 (were 32/30/24px), so the levels stand apart.
- Layout v2 on wide screens: the hero title may be 20ch wide instead of 14ch, three lines instead of five, so both buttons show without scrolling.
- Layout v2 on phones: the hero's trust badges are three short lines instead of three columns ("4 toimipistettä" ran over the screen edge), and the choice cards' buttons are full width like the hero's.
- Choice cards: the example prices are three columns (apartment type above its price), and the opening hours do not break inside "8–16".

### Fixed

- Choice cards: `.mh-landing p` and `.mh-landing h3` added bottom margins on top of the flex gap, so the gaps were 16, 24 and 32px; now one 16–20px gap, and the button still sits at the bottom of both cards.

## [1.2.33] - 2026-10-30

### Added

- Opt-in "v2" layout for one page (`inc/LayoutV2.php`): post meta `_mh_v2` = 1 adds the body class `mh-v2`. On phones and tablets that page then uses a larger type scale (body text 17px instead of 13px, H1 30px, H2 24px, H3 20px, mobile menu 16px) and taller floating buttons. On phones the landing hero hides the mascot and stacks full-width buttons, so the title and both actions fit on the first screen. From the front page review of 30.10.2026: in GA4 (2.–29.10.) the front page converted 5.0 % of phone sessions against 12.4 % on desktops. Used first on the password protected front page preview, before the whole site.
- `[hintahaarukka tyyppi="kaksio"]` prints one price range from the price table for running text ("450–600 €"); `laatikot="1"` gives the price with boxes.
- Choice cards: a row of example prices, underlined links inside a card and a business line under the cards (`.mh-choice-card__prices`, `.mh-choices__business`).

### Changed

- The choice section's title follows the theme's H2 size, since it now comes after the hero instead of being the page's H1.

## [1.2.32] - 2026-10-30

### Added

- Styles for a two-choice start on a landing page (`.mh-choices`, `.mh-choice-card`): two equal flat cards under the title, side by side on wide screens and stacked on phones, a yellow icon chip, and the button at the bottom of each card. From the client's proposal for the front page (30.10.2026): "Haluan tietää muuton hinnan" (yellow button to Tilaamuutto.fi) and "Haluan asiantuntijan apua muuttooni" (black button to the quote form). Used first on a password protected copy of the front page.

## [1.2.31] - 2026-10-30

### Removed

- The calculator's comparison lines ("+ … € täyden palvelun muutolla verrattuna samankokoisen asunnon pakettiin …" and "Muita paketteja verrataan tähän hintaan…") and the "Lue lisää" list under its buttons (Tapio 30.10.2026). The internal links inside the result stay.
- The unused `[me_vs_muut]` comparison shortcode with its script and styles (`interactive_comparison_rows()`, `interactive_comparison_topics()`).

## [1.2.30] - 2026-10-30

### Changed

- Calculator result, fewer words (Tapio 30.10.2026): the lines "Summa sisältää vain hinnoitellut rivit", "Mukana summassa", "Hinta-arvio perustuu julkaistuun hinnastoon…", "Kuluttajahinnat sisältävät ALV:n" and the explanation of how the household deduction is counted are gone. "Tilaa heti -palvelusta" became "Tilaamuutto.fi-palvelussa" (a link).

### Added

- Internal links in the calculator: the package, cleaning, piano and safe, furniture assembly and box rental names in the result link to their pages, "Pelican Self Storagen" links to the Pelican Self Storage page (/varastointi/), "Kotitalousvähennys" links to the price page's "Hinnan ehdot", and a "Lue lisää" list under the buttons links to the price page, full service, boxes, cleaning, piano and safe, storage and the FAQ. Addresses come from `interactive_page_links()` (filter `mh_interactive_page_links`); a page that is not published is left out.

## [1.2.29] - 2026-10-30

### Changed

- Calculator, "Vuokraan laatikot erikseen": the visitor is no longer asked for the number of boxes, the rental time or the distance (Tapio 30.10.2026: "emme voi kysyä näitä asioita ihmisiltä"). They are calculated from the apartment size: the number of boxes is the one the Tilaamuutto.fi order flow recommends (20 up to 29 m², 25, 30, 40, 50 and 60 up to 80 m², 0.7 per m² above), the rental time is the 14 days that flow uses, and delivery and pick-up are shown as a range, 2 × 25–40 €. One line under the checkbox tells what is assumed.

## [1.2.28] - 2026-10-30

### Changed

- The calculator's result panel is easier to read, above all on a phone (Tapio 30.10.2026: "todella vaikea lukea"). The price comes first with its footnote under it; the extra cost of a package is on its own bold line with what it is compared against under it; each group (included in the sum, not included, notes) starts with a hairline and a label; "Valittu paketti" is a small heading above the package name; on a phone the price sits under the name in a larger size instead of beside it; the text is 17px and the notes 16px. On wider screens the prices stay in a right hand column. No wording changed.

## [1.2.27] - 2026-10-30

### Changed

- Price calculator on phones and tablets: the price floats at the bottom of the screen with a "Pyydä tarjous" button while the controls are on screen and the result is not (it hides when the result panel is in view, and the site's own floating buttons step aside while it shows). The spacing of the controls follows one rhythm: 2rem between groups, 0.5rem from a title to its rows, 16px text for the rows.
- The comparison facts no longer promise an instant quote (the automatic quote was switched off on 30.10.2026): the price comes at once from Tilaamuutto.fi, a quote from the quote request.

## [1.2.26] - 2026-10-29

### Changed

- The calculator's "Pyydä tarjous" button opens the quote form with the choices filled in: apartment size, full service (the Pakkauspalvelu switch), cleaning, furniture assembly, piano or safe, storage, the number of boxes, and a short summary with the calculator's estimate in Lisätiedot. Only whitelisted values from the address are used, and a note above the form says the fields were filled in (`client.js`, `inc/InteractiveSections.php` unchanged).
- The calculator's result panel reads more easily: one text size, prices in a right hand column, lines no longer than 40rem, more space between groups, a labelled "Huomioitavaa" list, and two repeated sentences removed.
- Password protected pages: the password field has a border and the button matches the theme's buttons (`content.css`).

## [1.2.25] - 2026-10-26

### Added

- `[me_vs_muut]`: a filterable "Me vs muut" comparison. Eight rows (price, insurance, damage and packing, service area, environment, offer, cancellation, services) with Muuttohaukat's facts quoted from the site and a neutral question to ask any moving company; the visitor ticks the topics that matter.
- `[hintalaskuri]`: a price calculator. The visitor picks the apartment size (20–150 m²), one of four packages (with or without boxes, normal or full service), extra services (cleaning with the household deduction estimate, piano or safe, furniture assembly, storage) and separate box rental with delivery. The result updates at once and lists what is and is not in the sum. All numbers come from the theme's own tables (`price_rows()`, `box_rental_products()`, `box_delivery()`); the full service price is 2.15 times the base price rounded to 5 €, which reproduces the published full service table on every row.
- Both print the head and body columns of a `.mh-text-sec` and load `assets/css/interactive.css` and `assets/js/interactive.js` only on pages that use them (`inc/InteractiveSections.php`). Filters: `mh_interactive_data`, `mh_interactive_comparison_rows`, `mh_interactive_comparison_topics`.

## [1.2.24] - 2026-10-21

### Fixed

- A LibreForm submission that names no existing form (bots, often with SQL in `_formId`) is refused with HTTP 400 before LibreForm runs, using LibreForm's own form lookup, so a real form is never refused. LibreForm's catch block had logged "Undefined variable $useFallback" for each of them (321 times in four weeks), and the theme logged them as failed submissions; refused unknown forms are no longer logged.
- The submission logger no longer warns "Array to string conversion" when `_formId` is sent as an array.

## [1.2.23] - 2026-10-19

### Changed

- Price table row over 100 m²: 1100–2200 € with boxes (Tapio 19.10.2026). Every row now has a price in both columns.

## [1.2.22] - 2026-10-19

### Changed

- Price table row 30–39 m²: 350–550 € without boxes (Tapio 19.10.2026), so the row has a price in both columns.

## [1.2.21] - 2026-10-19

### Added

- Price table row 30–39 m² (yksiö tai pieni kaksio): 400–650 € with boxes (Tapio 19.10.2026). No price without boxes was given, so that cell asks for a quote. It shows on every page with the price table and in the price page's offers.

## [1.2.20] - 2026-10-19

### Fixed

- The over 100 m² row's 1000–2000 € is the moving price ("Muuttopalvelu"), as on the Omakotitalo example card, not the price with boxes; the boxes cell asks for a quote until a price is given. The example card now takes its figure from that row.

## [1.2.19] - 2026-10-19

### Added

- Price table row "yli 100 m² omakotitalo": 1000–2000 € with boxes, the figure the price page and the example cards already give for detached house moves (Tapio 19.10.2026: fill the price page's missing information). It shows on every page with the price table and in the price page's offers; the full-service table scales it like the other rows.

## [1.2.18] - 2026-10-19

### Added

- Gravity Forms offer forms accept a date typed as vvvv-kk-pp (2026-11-15), the way browser agents and assistants write dates: it is rewritten as pp.kk.vvvv before validation instead of failing with "Please enter a valid date" (`mh_gf_dynamics_accept_iso_dates()` in `inc/gf-dynamics/form-rules.php`, date picker fields of forms with an active Dynamics feed).

## [1.2.17] - 2026-10-19

### Added

- Each office in `offices()` has its Google Maps place (`profile`, a `?cid=` link to the verified Business Profile), its pin (`geo`) and the maakunnat its towns lie in (`regions`). The office markup carries them as `sameAs`, `hasMap`, `geo` and, on the office's own page, `areaServed`; city pages keep their town as `areaServed`.
- The front page prints the full organization (`organization_schema()`: address, phone, e-mail, Y-tunnus, founding year 1992, founder, staff count and the four offices), as the company page already did, plus a customer service `contactPoint`. The search trends and AI agents report of 19.10.2026 found the front page's Organization had only a name, logo and URL.

### Changed

- The price page's `areaServed` is the maakunnat the offices serve (Uusimaa, Kanta-Häme, Päijät-Häme, Pirkanmaa, Varsinais-Suomi) instead of all of Finland, since the price list applies within the offices' local areas.
- The office markup and the services' `provider` point to the organization's `@id`, so every page describes the same company.

## [1.2.16] - 2026-10-19

### Fixed

- Example price cards: the landing text margins no longer add up between the price and the lines under it; one 10px rhythm inside each card.

## [1.2.15] - 2026-10-19

### Added

- `[esimerkkihinnat]` (`inc/ExamplePrices.php`): worked examples of what a move costs (yksiö, kaksio, kolmio, perheasunto, a full-service kaksio and a detached house), generated from `price_rows()` and the full-service multiplier 2.15 (Muuttohaukat 12.10.2026: "Voidaan generoida hinta-haarukan perusteella"). They are examples from the price ranges, not realized moves, and follow any price change. Styles `.mh-examples` in landing.css.

## [1.2.14] - 2026-10-12

### Changed

- Brand colours (Muuttohaukat 12.10.2026: "Tilaa muutto keltainen, pyydä tarjous musta"): a button that links to tilaamuutto.fi is yellow and a button that asks for a quote (`/tarjouspyynto…`, `#tarjous…`) is black, whatever colour it was given. The link decides, so the Painike block, the floating CTA, core buttons with the theme's styles and the article CTA all follow it. On a yellow or black section the button of the section's own colour turns white.
- Muuttosiivous is offered on a city page only where the cleaning crews reach: the town has its own cleaning page, or it lies within 50 km (as the crow flies, from the new `_mh_coords` meta) of a town that has one (Muuttohaukat 12.10.2026: no cleaning in Pori, Savonlinna or Lappeenranta, "Yltää about 50km säteellä").

### Added

- `[muuttolaatikot_toimitus]` with a place but no office: delivery is agreed case by case and the boxes can be picked up from any office, instead of delivery days and prices counted from an office (for towns outside the offices' local areas).
- The box directory groups towns whose moving page has no office as "Muut paikkakunnat", before "Maakunnat".

## [1.2.13] - 2026-10-12

### Removed

- The "date pickers start from tomorrow" script of 1.2.12: it set a jQuery UI option, and Gravity Forms 3 has its own date picker without a minimum date setting, so it did nothing. The server-side date rules stay.

## [1.2.12] - 2026-10-12

### Fixed

- Gravity Forms → Dynamics: a "no" answer mapped to a checkbox key (the Ei radio of the lift questions) is left out like an unchecked box. It used to become LähtöHissi / KohdeHissi "on", so a flat without a lift would have reached Dynamics as one with a lift.

### Added

- `inc/gf-dynamics/form-rules.php`, for every Gravity Forms form with an active Dynamics feed (fields found through the feed's field map): the LibreForm date rules on the server (not today, not in the past, return not before delivery or the move); ?date=Y-m-d from the moving day calendar fills the date field with dynamic population parameter `date`; a successful submission pushes `gf_lead_submit` to the dataLayer for Google Tag Manager.

## [1.2.11] - 2026-10-07

### Fixed

- `price_rows()`: 40-59 m² (kaksio) without boxes is 450-600 € instead of a "Pyydä tarjous" link in every price table (Tapio 7.10.2026). 450 is the lower bound the old price page stated (it said 450–450 €); 600 keeps the 100 € gap to the price with boxes that the other sizes have. The full-service table (multiplier 2.15) shows 970-1290 €.

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
