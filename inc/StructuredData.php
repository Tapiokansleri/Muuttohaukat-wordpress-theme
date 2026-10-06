<?php
/**
 * Company facts and structured data (audit 3.8).
 *
 * - Office pages and the contact page: MovingCompany per office
 * - Price page: the price list as offers
 * - FAQ page: FAQPage built from the page's own questions and answers
 * - Main service pages: Service
 * - Contact page: offices and company details as a visible section
 *
 * The Organization itself comes from The SEO Framework's knowledge graph.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Verified company facts. Business ID from tilaamuutto.fi's terms; history,
 * people, volumes and revenue from the company's own news on this site
 * (CEO 1.11.2022: post 2397, 2025 results: post 8702, award: post 512).
 */
function company_facts(): array {
  return apply_filters('mh_company_facts', [
    'name'         => 'Muuttohaukat',
    'legal_name'   => 'Muuttohaukat Oy',
    'business_id'  => '0887272-7',
    'vat_id'       => 'FI08872727',
    'phone'        => '010 400 4500',
    'email'        => 'muuttohaukat@muuttohaukat.com',
    'founded'      => '1992',
    'founded_in'   => 'Hyvinkää',
    'founder'      => 'Matti Vivolin',
    'ceo'          => 'Totte Vivolin',
    'ceo_since'    => '2022',
    'moves_total'  => 'yli 100 000',
    'moves_year'   => ['year' => '2025', 'count' => 'yli 10 000'],
    'revenue_year' => ['year' => '2025', 'amount' => '6,3 miljoonaa euroa'],
    'employees'    => 'yli 100',
  ]);
}

function print_json_ld(array $data): void {
  echo '<script type="application/ld+json">' . wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}

function schema_provider(): array {
  $facts = company_facts();

  return [
    '@type'     => 'MovingCompany',
    'name'      => $facts['name'],
    'legalName' => $facts['legal_name'],
    'vatID'     => $facts['vat_id'],
    'telephone' => $facts['phone'],
    'url'       => home_url('/'),
  ];
}

function service_schema(string $name, string $path, array $extra = []): array {
  return array_merge([
    '@context'   => 'https://schema.org',
    '@type'      => 'Service',
    'name'       => $name,
    'serviceType'=> $name,
    'provider'   => schema_provider(),
    'areaServed' => ['@type' => 'Country', 'name' => 'Suomi'],
    'url'        => home_url($path),
  ], $extra);
}

/**
 * The home move price list (price_rows()) as offers. Rows without a
 * confirmed price are left out.
 */
function price_schema(): array {
  $offers = [];
  foreach (price_rows() as $row) {
    foreach (['basic' => 'ilman muuttolaatikoita', 'boxes' => 'muuttolaatikoiden kanssa'] as $key => $label) {
      if (empty($row[$key]) || !preg_match('/(\d+)\s*-\s*(\d+)/', (string) $row[$key], $m)) {
        continue;
      }
      $offers[] = [
        '@type'              => 'Offer',
        'name'               => sprintf('Kotimuutto %s (%s), %s', $row['size'], $row['type'], $label),
        'priceCurrency'      => 'EUR',
        'priceSpecification' => [
          '@type'         => 'PriceSpecification',
          'minPrice'      => (int) $m[1],
          'maxPrice'      => (int) $m[2],
          'priceCurrency' => 'EUR',
        ],
      ];
    }
  }

  return service_schema('Kotimuutto', '/kotimuutto/muuttopalvelun-hinta/', ['offers' => $offers]);
}

/**
 * FAQPage from the page's own text: a heading that ends with a question mark
 * is a question, and the text up to the next heading is its answer.
 */
function faq_schema(\WP_Post $post): ?array {
  $html = '';
  // Beaver Builder data only while the page is still built with it.
  $data = get_post_meta($post->ID, '_fl_builder_enabled', true) ? get_post_meta($post->ID, '_fl_builder_data', true) : null;
  if (is_array($data)) {
    foreach ($data as $node) {
      if (is_object($node) && isset($node->settings->type) && $node->settings->type === 'rich-text') {
        $html .= (string) ($node->settings->text ?? '');
      }
    }
  }
  if ($html === '') {
    $html = (string) $post->post_content;
  }
  if ($html === '' || !class_exists('DOMDocument')) {
    return null;
  }

  $doc = new \DOMDocument();
  $previous = libxml_use_internal_errors(true);
  $doc->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>');
  libxml_clear_errors();
  libxml_use_internal_errors($previous);
  $root = $doc->getElementsByTagName('div')->item(0);
  if (!$root) {
    return null;
  }

  $items = [];
  $question = null;
  $answer = '';
  $flush = function () use (&$items, &$question, &$answer) {
    $text = trim(preg_replace('/\s+/u', ' ', $answer));
    if ($question !== null && $text !== '') {
      $items[] = [
        '@type'          => 'Question',
        'name'           => $question,
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $text],
      ];
    }
    $question = null;
    $answer = '';
  };

  // Headings and text blocks in document order, also inside the section
  // groups of Gutenberg pages (a paragraph inside a list item counts once).
  $xpath = new \DOMXPath($doc);
  $nodes = $xpath->query('.//*[self::h2 or self::h3 or self::h4 or self::p or self::li or self::summary][not(ancestor::li)]', $root);
  // A page that uses open/close items asks its questions there; its headings
  // are topics, even when they end with a question mark.
  $summaries_only = $xpath->query('.//summary', $root)->length > 0;
  foreach ($nodes as $node) {
    $text = trim(preg_replace('/\s+/u', ' ', $node->textContent));
    if ($node instanceof \DOMElement && in_array(strtolower($node->tagName), ['h2', 'h3', 'h4', 'summary'], true)) {
      $flush();
      $is_question_tag = !$summaries_only || strtolower($node->tagName) === 'summary';
      if ($is_question_tag && $text !== '' && substr($text, -1) === '?') {
        $question = $text;
      }
      continue;
    }
    if ($question !== null) {
      $answer .= ' ' . $text;
    }
  }
  $flush();

  return $items ? ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items] : null;
}

/**
 * Pages that get structured data, by path.
 */
function structured_data_pages(): array {
  return apply_filters('mh_structured_data_pages', [
    'services' => [
      '/kotimuutto/'          => 'Kotimuutto',
      '/yritysmuutto/'        => 'Yritysmuutto',
      '/muuttosiivous/'       => 'Muuttosiivous',
      '/varastointi/'         => 'Varastointi',
      '/muuttolaatikot-ja-muuttotarvikkeet/' => 'Muuttolaatikoiden vuokraus',
      '/kassakaappikuljetus/' => 'Piano- ja kassakaappikuljetus',
    ],
    'prices'  => '/kotimuutto/muuttopalvelun-hinta/',
    'faq'     => '/usein-kysyttyja-kysymyksia/',
    'contact' => '/yhteystiedot/',
  ]);
}

function current_path(): string {
  $post = get_queried_object();
  return $post instanceof \WP_Post ? (string) wp_make_link_relative(get_permalink($post)) : '';
}

add_action('wp_head', function () {
  if (!is_singular()) {
    return;
  }
  $post = get_queried_object();
  if (!$post instanceof \WP_Post) {
    return;
  }

  $path = current_path();
  $pages = structured_data_pages();

  foreach (offices() as $office) {
    if ($office['page'] === $path || $path === $pages['contact']) {
      print_json_ld(office_schema($office));
    }
  }

  if (isset($pages['services'][$path])) {
    print_json_ld(service_schema($pages['services'][$path], $path));
  }

  if ($path === $pages['prices']) {
    print_json_ld(price_schema());
  }

  if ($path === $pages['faq']) {
    $faq = faq_schema($post);
    if ($faq) {
      print_json_ld($faq);
    }
  }
}, 20);

/**
 * The offices and company details as a section, for the contact page.
 */
function company_section_html(): string {
  $facts = company_facts();
  $hours = office_hours();

  ob_start();
  ?>
  <section class="mh-company-info" aria-labelledby="mh-company-info-title">
    <div class="mh-company-info__inner">
      <div class="mh-company-info__head">
        <h2 id="mh-company-info-title">Toimipisteet ja yritystiedot</h2>
        <p><?php echo esc_html(sprintf('%s · Y-tunnus %s · puh. %s', $facts['legal_name'], $facts['business_id'], $facts['phone'])); ?></p>
        <p><?php echo esc_html($hours['text']); ?></p>
        <p><a href="<?php echo esc_url(home_url('/yritystiedot/')); ?>">Kaikki yritystiedot</a></p>
      </div>
      <ul class="mh-company-info__offices">
        <?php foreach (offices() as $office) : ?>
          <li class="mh-company-info__office">
            <p class="mh-company-info__label"><?php echo esc_html($office['label']); ?></p>
            <p><?php echo esc_html($office['street']); ?><br><?php echo esc_html($office['zip'] . ' ' . $office['city']); ?></p>
            <p><a href="<?php echo esc_url(home_url($office['page'])); ?>"><?php echo esc_html($office['area']); ?></a></p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
  <?php

  return (string) ob_get_clean();
}

add_action('get_footer', function () {
  if (is_singular() && current_path() === structured_data_pages()['contact']) {
    echo company_section_html();
  }
}, 4);
