<?php
/**
 * Yritystiedot page (audit action 10): the company's facts on one page, for
 * customers and for AI assistants that otherwise take them from Kauppalehti
 * and Hansel, with the same facts as structured data.
 *
 * [muuttohaukat_yritystiedot] renders the facts from company_facts() and the
 * offices from offices(), so a change to either shows here, on the contact
 * page, in the footer and in the markup at once.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

const COMPANY_PAGE = '/yritystiedot/';

/**
 * Agreements and recognitions, each with the page that tells more.
 */
function company_credentials(): array {
  return apply_filters('mh_company_credentials', [
    ['text' => 'Hansel Oy:n dynaaminen hankintajärjestelmä: sopimus on kaikkien järjestelmään liittyneiden julkisten hankintayksiköiden käytettävissä', 'url' => '/julkisen-sektorin-muutot/'],
    ['text' => 'Ekokompassi-ympäristösertifikaatti ensimmäisenä muuttoalan yrityksenä, käytössä vuodesta 2013', 'url' => '/ymparisto/'],
    ['text' => 'Hyvinkään Vuoden Yritys 2017', 'url' => '/muuttohaukoille-vuoden-yrittaja-palkinto/'],
  ]);
}

function company_services(): array {
  return apply_filters('mh_company_services', [
    ['text' => 'Kotimuutto', 'url' => '/kotimuutto/'],
    ['text' => 'Yritysmuutto', 'url' => '/yritysmuutto/'],
    ['text' => 'Julkisen sektorin muutot', 'url' => '/julkisen-sektorin-muutot/'],
    ['text' => 'Muuttosiivous', 'url' => '/muuttosiivous/'],
    ['text' => 'Varastointi', 'url' => '/varastointi/'],
    ['text' => 'Muuttolaatikot', 'url' => '/muuttolaatikot-ja-muuttotarvikkeet/'],
    ['text' => 'Piano- ja kassakaappikuljetus', 'url' => '/kassakaappikuljetus/'],
  ]);
}

function company_page_html(): string {
  $f     = company_facts();
  $hours = office_hours();
  $rows  = [
    'Virallinen nimi' => $f['legal_name'],
    'Y-tunnus'        => $f['business_id'],
    'ALV-tunnus'      => $f['vat_id'],
    'Perustettu'      => sprintf('%s, %s, perheyritys', $f['founded'], $f['founded_in']),
    'Perustaja'       => sprintf('%s, hallituksen puheenjohtaja', $f['founder']),
    'Toimitusjohtaja' => sprintf('%s vuodesta %s', $f['ceo'], $f['ceo_since']),
    'Muutot'          => sprintf('%s muuttoa, vuonna %s %s muuttoa', $f['moves_total'], $f['moves_year']['year'], $f['moves_year']['count']),
    'Liikevaihto'     => sprintf('%s vuonna %s', $f['revenue_year']['amount'], $f['revenue_year']['year']),
    'Henkilöstö'      => sprintf('%s työntekijää', $f['employees']),
    'Puhelin'         => $f['phone'],
    'Sähköposti'      => $f['email'],
  ];

  ob_start();
  ?>
  <section class="mh-landing-section mh-landing-section--bg-white mh-company" id="perustiedot" aria-labelledby="mh-company-title">
    <div class="mh-landing__inner mh-company__inner">
      <div class="mh-company__col">
        <p class="mh-landing-kicker">Perustiedot</p>
        <h2 id="mh-company-title"><?php echo esc_html($f['legal_name']); ?> lyhyesti</h2>
        <p><?php echo esc_html(sprintf('%s perusti Muuttohaukat Oy:n Hyvinkäälle vuonna %s. Perheyritys on kasvanut valtakunnalliseksi muuttofirmaksi, joka on tehnyt %s muuttoa, ja se on Suomen suurin pelkästään kotimaan muuttopalveluihin erikoistunut yritys.', $f['founder'], $f['founded'], $f['moves_total'])); ?></p>
        <p><?php echo esc_html(sprintf('Toimitusjohtajana on vuodesta %s toiminut %s. Muuttoja tehdään neljästä toimipisteestä: Hyvinkäältä, Vantaalta, Tampereelta ja Turusta.', $f['ceo_since'], $f['ceo'])); ?></p>
      </div>
      <dl class="mh-company__facts">
        <?php foreach ($rows as $label => $value) : ?>
          <div class="mh-company__fact">
            <dt><?php echo esc_html($label); ?></dt>
            <dd>
              <?php if ($label === 'Puhelin') : ?>
                <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $value)); ?>"><?php echo esc_html($value); ?></a>
              <?php elseif ($label === 'Sähköposti') : ?>
                <a href="mailto:<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></a>
              <?php else : ?>
                <?php echo esc_html($value); ?>
              <?php endif; ?>
            </dd>
          </div>
        <?php endforeach; ?>
      </dl>
    </div>
  </section>

  <section class="mh-landing-section mh-landing-section--bg-yellow mh-company-offices" id="toimipisteet" aria-labelledby="mh-company-offices-title">
    <div class="mh-landing__inner">
      <div class="mh-company-offices__head">
        <p class="mh-landing-kicker">Toimipisteet</p>
        <h2 id="mh-company-offices-title">Neljä toimipistettä</h2>
        <p><?php echo esc_html($hours['text']); ?></p>
      </div>
      <ul class="mh-company-offices__list">
        <?php foreach (offices() as $office) : ?>
          <li class="mh-company-offices__office">
            <p class="mh-company-offices__label"><?php echo esc_html($office['label']); ?></p>
            <p><?php echo esc_html($office['street']); ?><br><?php echo esc_html($office['zip'] . ' ' . $office['city']); ?></p>
            <p><a href="<?php echo esc_url(home_url($office['page'])); ?>"><?php echo esc_html(ucfirst($office['area'])); ?></a></p>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>

  <section class="mh-landing-section mh-landing-section--bg-white mh-company" id="sopimukset" aria-labelledby="mh-company-credentials-title">
    <div class="mh-landing__inner mh-company__inner">
      <div class="mh-company__col">
        <p class="mh-landing-kicker">Sopimukset ja tunnustukset</p>
        <h2 id="mh-company-credentials-title">Sopimukset ja tunnustukset</h2>
        <ul class="mh-hero-checklist">
          <?php foreach (company_credentials() as $item) : ?>
            <li><a href="<?php echo esc_url(home_url($item['url'])); ?>"><?php echo esc_html($item['text']); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="mh-company__col">
        <p class="mh-landing-kicker">Palvelut</p>
        <h2>Mitä teemme</h2>
        <ul class="mh-hero-checklist">
          <?php foreach (company_services() as $item) : ?>
            <li><a href="<?php echo esc_url(home_url($item['url'])); ?>"><?php echo esc_html($item['text']); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </section>
  <?php

  return (string) ob_get_clean();
}

add_shortcode('muuttohaukat_yritystiedot', __NAMESPACE__ . '\\company_page_html');

/**
 * The organization with its history, people and offices. Shares the @id of
 * The SEO Framework's Organization node, so the two describe one company.
 */
function organization_schema(): array {
  $f = company_facts();
  $hq = office('hyvinkaa');

  $offices = array_map(function ($office) {
    $schema = office_schema($office);
    unset($schema['@context'], $schema['parentOrganization']);
    return $schema;
  }, array_values(offices()));

  return [
    '@context'         => 'https://schema.org',
    '@type'            => 'MovingCompany',
    '@id'              => home_url('/#/schema/Organization'),
    'name'             => $f['name'],
    'legalName'        => $f['legal_name'],
    'taxID'            => $f['business_id'],
    'vatID'            => $f['vat_id'],
    'url'              => home_url('/'),
    'telephone'        => $f['phone'],
    'email'            => $f['email'],
    'foundingDate'     => $f['founded'],
    'foundingLocation' => ['@type' => 'Place', 'name' => $f['founded_in']],
    'founder'          => ['@type' => 'Person', 'name' => $f['founder']],
    'numberOfEmployees'=> ['@type' => 'QuantitativeValue', 'minValue' => 100],
    'award'            => 'Hyvinkään Vuoden Yritys 2017',
    'address'          => [
      '@type'           => 'PostalAddress',
      'streetAddress'   => $hq['street'],
      'postalCode'      => $hq['zip'],
      'addressLocality' => $hq['city'],
      'addressCountry'  => 'FI',
    ],
    'areaServed'       => ['@type' => 'Country', 'name' => 'Suomi'],
    'department'       => $offices,
  ];
}

add_action('wp_head', function () {
  if (is_singular() && current_path() === COMPANY_PAGE) {
    print_json_ld(organization_schema());
  }
}, 20);
