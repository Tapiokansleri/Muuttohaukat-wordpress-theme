<?php
/**
 * Moving box page sections, modelled on the box hub
 * /muuttolaatikot-ja-muuttotarvikkeet/ (page 8265), whose products, prices,
 * delivery terms and packing advice they repeat on the box city pages:
 *
 *  [muuttolaatikot_vuokra]        rented boxes and trolleys with photos and day prices
 *  [muuttolaatikot_toimitus]      delivery days, time windows and delivery prices
 *  [muuttolaatikot_tarvikkeet]    cardboard boxes and packing supplies to buy (audit action 3)
 *  [muuttolaatikot_pakkausohje]   how to pack the boxes
 *  [muuttolaatikot_hinnat]        the rental prices as a plain table
 *
 * Every section has the same two-column shape as the landing sections: the
 * eyebrow, heading and intro on the left, the content on the right. The data
 * lives in the functions below, so a price changes in one place.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

const BOX_ORDER_URL = 'https://tilaamuutto.fi/tilaus/laatikot';

/**
 * Rented items as on the box hub: day price, photo and a short description.
 */
function box_rental_products(): array {
  return apply_filters('mh_box_rental_products', [
    ['name' => 'Kotimuuttolaatikko (kääntölaatikko)', 'price' => '0,19 €', 'unit' => 'laatikko / päivä', 'image' => 4496,
      'text' => 'Kodin pientavaroille ja särkyville tavaroille. Kääntämällä laatikon täydet laatikot pinoutuvat päällekkäin.'],
    ['name' => 'Yritysmuuttolaatikko (sankalaatikko)', 'price' => '0,19 €', 'unit' => 'laatikko / päivä', 'image' => 4486,
      'text' => 'Mapeille ja toimistotarvikkeille. Metallisangat pinoamiseen, kantoaukot laatikon molemmissa päissä.'],
    ['name' => 'Tietoturvalaatikko', 'price' => '2,95 €', 'unit' => 'laatikko / päivä', 'image' => 4481,
      'text' => 'Tietoturvamateriaalin siirtoihin, erityisesti yritysmuutoissa.'],
    ['name' => 'Datakaappi', 'price' => '9,61 €', 'unit' => 'kpl / päivä', 'image' => 4497,
      'text' => 'Tietokoneiden, tulostimien ja muiden ICT-laitteiden siirtoihin.'],
    ['name' => 'Muuttorullakko', 'price' => '3,50 €', 'unit' => 'kpl / päivä', 'image' => 4504,
      'text' => 'Siirtää suuren määrän tavaraa kerralla.'],
    ['name' => 'Rullakon takaseinä', 'price' => '0,99 €', 'unit' => 'kpl / päivä', 'image' => 4503,
      'text' => 'Lisävaruste, joka helpottaa rullakon lastausta.'],
    ['name' => 'Rullakon välihylly', 'price' => '0,99 €', 'unit' => 'kpl / päivä', 'image' => 4505,
      'text' => 'Lisävaruste, jolla alimmaksi lastatut tavarat pysyvät ehjinä koko kuljetuksen ajan.'],
  ]);
}

/**
 * Rental prices (per box or per piece, per day) for the plain price table.
 */
function box_rental_prices(): array {
  $rows = array_map(fn($p) => ['name' => $p['name'], 'price' => $p['price'], 'unit' => $p['unit']], box_rental_products());
  return apply_filters('mh_box_rental_prices', $rows);
}

/**
 * Delivery and pick-up of boxes and supplies, as on the box hub.
 */
function box_delivery(): array {
  return apply_filters('mh_box_delivery', [
    'days'    => 'Tiistai ja torstai',
    'windows' => 'Aamupäivä klo 8–12 tai iltapäivä klo 12–17',
    'prices'  => [
      ['label' => 'Alle 12 km', 'price' => '25 €', 'unit' => 'suunta'],
      ['label' => '12–30 km', 'price' => '40 €', 'unit' => 'suunta'],
      ['label' => 'Yli 30 km', 'price' => 'Tuntiveloitus', 'unit' => ''],
    ],
  ]);
}

/**
 * Packing advice from the box hub.
 */
function box_packing_tips(): array {
  return apply_filters('mh_box_packing_tips', [
    'Jätä laatikon yläreunaan noin 4 cm tyhjää, niin täydet laatikot voi pinota turvallisesti päällekkäin.',
    'Pinoa täysiä laatikoita korkeintaan neljä päällekkäin.',
    'Pinon ylimmän laatikon voi täyttää yli laitojen, mutta ei liian täyteen: tavarat voivat pudota, kun pinoa kallistetaan.',
    'Älä jätä irtonaisia papereita laatikon päällimmäisiksi.',
    'Merkitse laatikot huoneen mukaan merkintätarroilla, niin tavarat löytyvät heti oikeasta huoneesta.',
  ]);
}

/**
 * Accessory slugs in display order: the products /muuttotarvikkeet/ sells.
 */
function box_supply_slugs(): array {
  return apply_filters('mh_box_supply_slugs', [
    'pahvilaatikko',
    'astia-ja-kirjalaatikot',
    'kuplamuovi',
    'kuplamuovirulla',
    'pakkauspaperi',
    'muuttoteippi',
    'kasikiristekalvo',
    'kasikiristekalvo-100mm',
    'merkintatarrat',
    'muuttosakki',
    'lattiansuojamuovi',
  ]);
}

/**
 * Product photos from the box hub for the accessories that have no
 * featured image of their own.
 */
function box_supply_images(): array {
  return apply_filters('mh_box_supply_images', [
    'kuplamuovirulla'   => 4487,
    'pakkauspaperi'     => 4482,
    'muuttoteippi'      => 4498,
    'lattiansuojamuovi' => 4127,
  ]);
}

/**
 * The first sentence of a product's text, after its name heading.
 */
function box_supply_summary(\WP_Post $post): string {
  // Skip the hero eyebrow, a price-only line and shortcodes of the renewed product pages.
  $html = preg_replace([
    '#<h[1-6][^>]*>.*?</h[1-6]>#s',
    '#<p class="mh-landing-eyebrow">.*?</p>#s',
    '#<p[^>]*>\s*<strong>[^<]*€[^<]*</strong>\s*</p>#su',
    '#\[[a-z_][^\]]*\]#',
  ], ' ', $post->post_content);
  $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES, 'UTF-8')));
  return preg_match('/^(.+?[.!?])(\s|$)/u', $text, $m) ? $m[1] : $text;
}

/**
 * A product photo; the product name is the alt text when the image has none.
 */
function box_product_image(int $id, string $name, string $class): string {
  if (!$id || !wp_attachment_is_image($id)) {
    return '';
  }
  $alt = trim((string) get_post_meta($id, '_wp_attachment_image_alt', true));
  return wp_get_attachment_image($id, 'medium', false, [
    'class'   => $class,
    'alt'     => $alt !== '' ? $alt : $name,
    'loading' => 'lazy',
    'sizes'   => '(max-width: 640px) 50vw, 260px',
  ]);
}

/**
 * The shared section frame: intro column and content column.
 *
 * @param array $intro kicker, title, paragraphs (escaped HTML allowed), button [text, url, target]
 */
function box_section(string $modifier, array $intro, string $body): string {
  $id = 'mh-box-sec-' . $modifier;
  ob_start();
  ?>
  <section class="mh-box-sec mh-box-sec--<?php echo esc_attr($modifier); ?> not-prose" aria-labelledby="<?php echo esc_attr($id); ?>">
    <div class="mh-box-sec__intro">
      <p class="mh-box-sec__kicker"><?php echo esc_html($intro['kicker']); ?></p>
      <h2 id="<?php echo esc_attr($id); ?>" class="mh-box-sec__title"><?php echo esc_html($intro['title']); ?></h2>
      <?php foreach ($intro['paragraphs'] ?? [] as $paragraph) : ?>
        <p><?php echo wp_kses_post($paragraph); ?></p>
      <?php endforeach; ?>
      <?php if (!empty($intro['button'])) :
        $external = str_starts_with($intro['button']['url'], 'http') && !str_contains($intro['button']['url'], wp_parse_url(home_url(), PHP_URL_HOST));
        ?>
        <p><a class="mh-painike mh-painike--yellow" href="<?php echo esc_url($intro['button']['url']); ?>"<?php echo $external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html($intro['button']['text']); ?></a></p>
      <?php endif; ?>
    </div>
    <div class="mh-box-sec__body">
      <?php echo $body; // Built from escaped parts by the callers. ?>
    </div>
  </section>
  <?php
  return (string) ob_get_clean();
}

add_shortcode('muuttolaatikot_vuokra', function () {
  $cards = '';
  foreach (box_rental_products() as $product) {
    $cards .= '<li class="mh-box-card">'
      . box_product_image((int) $product['image'], $product['name'], 'mh-box-card__img')
      . '<h3 class="mh-box-card__name">' . esc_html($product['name']) . '</h3>'
      . '<p class="mh-box-card__price"><strong>' . esc_html($product['price']) . '</strong> / ' . esc_html($product['unit']) . '</p>'
      . '<p class="mh-box-card__text">' . esc_html($product['text']) . '</p>'
      . '</li>';
  }

  return box_section('vuokra', [
    'kicker'     => 'Vuokraa',
    'title'      => 'Vuokrattavat laatikot ja rullakot',
    'paragraphs' => [
      esc_html('Muovilaatikot ja rullakot vuokrataan päivähinnalla. Tuomme ne kotiovelle ja haemme pois muuton jälkeen, tai noudat ne itse toimipisteestä ilman eri varausta.'),
    ],
    'button'     => ['text' => 'Tilaa laatikot', 'url' => BOX_ORDER_URL],
  ], '<ul class="mh-box-cards">' . $cards . '</ul>');
});

/**
 * [muuttolaatikot_toimitus iness="Espoossa" place="Espoo" office="vantaa" km="30" time="29 min"]
 * All attributes are optional; with place, office and km the list also
 * shows the drive from the serving office.
 */
add_shortcode('muuttolaatikot_toimitus', function ($atts) {
  $atts = shortcode_atts(['iness' => '', 'place' => '', 'office' => '', 'km' => '', 'time' => ''], $atts, 'muuttolaatikot_toimitus');
  $delivery = box_delivery();

  $rows = [
    ['Toimituspäivät', $delivery['days']],
    ['Aikaikkuna', $delivery['windows']],
  ];
  foreach ($delivery['prices'] as $price) {
    $rows[] = [$price['label'], '<strong>' . esc_html($price['price']) . '</strong>' . ($price['unit'] ? ' / ' . esc_html($price['unit']) : ''), true];
  }
  $office = $atts['office'] ? office($atts['office']) : null;
  if ($office && $atts['place'] && (int) $atts['km'] > 0) {
    $drive = sprintf('noin %d km', (int) $atts['km']) . ($atts['time'] ? ', ' . $atts['time'] : '');
    $rows[] = [sprintf('%s – %s', $office['city'], $atts['place']), $drive];
  }

  $list = '';
  foreach ($rows as $row) {
    $value = !empty($row[2]) ? $row[1] : esc_html($row[1]);
    $list .= '<div class="mh-box-rows__row"><dt>' . esc_html($row[0]) . '</dt><dd>' . $value . '</dd></div>';
  }

  $title = $atts['iness'] ? sprintf('Toimitus ja nouto %s', $atts['iness']) : 'Toimitus ja nouto kotiovelle';
  $lead = 'Toimitamme ja noudamme laatikot ja tarvikkeet tiistaisin ja torstaisin. Valitset itse, tuodaanko ne aamupäivällä vai iltapäivällä.';
  if ($office) {
    $lead .= ' ' . sprintf('Toimitushinta määräytyy ajomatkasta %s.', $office['from']);
  }

  return box_section('toimitus', [
    'kicker'     => 'Toimitus',
    'title'      => $title,
    'paragraphs' => [
      esc_html($lead),
      esc_html('Ajamme laatikkokuljetukset kahtena päivänä viikossa, jotta reitit kuormittavat ympäristöä vähemmän ja saat tarkemman toimitusajan.'),
    ],
  ], '<dl class="mh-box-rows">' . $list . '</dl>');
});

add_shortcode('muuttolaatikot_tarvikkeet', function () {
  $images = box_supply_images();
  $items = '';
  foreach (box_supply_slugs() as $slug) {
    $post = get_page_by_path($slug, OBJECT, 'accessory');
    if (!$post || $post->post_status !== 'publish') {
      continue;
    }
    $name = get_the_title($post);
    $image = (int) (get_post_thumbnail_id($post) ?: ($images[$slug] ?? 0));
    $items .= '<li class="mh-box-item">'
      . box_product_image($image, wp_strip_all_tags($name), 'mh-box-item__img')
      . '<span class="mh-box-item__text">'
      . '<a class="mh-box-item__name" href="' . esc_url(get_permalink($post)) . '">' . esc_html($name) . '</a>'
      . '<span class="mh-box-item__desc">' . esc_html(box_supply_summary($post)) . '</span>'
      . '</span></li>';
  }
  if ($items === '') {
    return '';
  }

  return box_section('tarvikkeet', [
    'kicker'     => 'Osta samalla',
    'title'      => 'Pahvilaatikot ja pakkaustarvikkeet',
    'paragraphs' => [
      esc_html('Muovilaatikot vuokrataan, mutta pahvilaatikot ja pakkaustarvikkeet jäävät sinulle. Tilaa ne samalla toimituksella laatikoiden kanssa tai nouda toimipisteestä ilman eri varausta.'),
    ],
    'button'     => ['text' => 'Tilaa tarvikkeita', 'url' => home_url('/muuttotarvikkeet/#tilaa')],
  ], '<ul class="mh-box-items">' . $items . '</ul>');
});

add_shortcode('muuttolaatikot_pakkausohje', function () {
  $tips = '';
  foreach (box_packing_tips() as $tip) {
    $tips .= '<li>' . esc_html($tip) . '</li>';
  }

  return box_section('pakkausohje', [
    'kicker'     => 'Pakkaaminen',
    'title'      => 'Näin pakkaat muuttolaatikot',
    'paragraphs' => [
      esc_html('Oikein pakatut laatikot on nopea kantaa ja pinota, ja tavarat pysyvät ehjinä koko muuton ajan.'),
    ],
  ], '<ol class="mh-box-tips">' . $tips . '</ol>');
});

add_shortcode('muuttolaatikot_hinnat', function () {
  ob_start();
  ?>
  <table class="mh-box-prices">
    <thead><tr><th scope="col">Vuokrattava</th><th scope="col">Hinta</th></tr></thead>
    <tbody>
      <?php foreach (box_rental_prices() as $row) : ?>
        <tr><th scope="row"><?php echo esc_html($row['name']); ?></th><td><strong><?php echo esc_html($row['price']); ?></strong> / <?php echo esc_html($row['unit']); ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php
  return (string) ob_get_clean();
});
