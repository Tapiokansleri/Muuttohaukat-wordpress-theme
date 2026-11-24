<?php
/**
 * Template Name: Artikkeli: opas (hero ja osiot)
 * Template Post Type: post, page
 *
 * The blog format with its own template: a hero, sections that alternate yellow and white, tools, a closing call to
 * action and related reading. Opt-in (choose this template); see inc/Guide.php for the content rules and the meta keys.
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if (!defined('ABSPATH')) {
  exit;
}

get_header();

while (have_posts()) {
  the_post();
  $post = get_post();

  // A protected article shows only the password form: nothing of the article, not even the title.
  if (post_password_required($post)) {
    echo '<main id="content" class="mh-guide" role="main"><section class="mh-guide-sec mh-guide-sec--white"><div class="mh-guide-wrap">'
      . get_the_password_form($post) . '</div></section></main>';
    continue;
  }

  $sections   = guide_sections($post);
  $rendered   = implode(' ', array_map(fn($s) => $s['lead'] . $s['body'], $sections));
  $title      = wptexturize($post->post_title);
  $standfirst = (string) get_post_meta($post->ID, '_mh_guide_standfirst', true);
  $alt        = (string) get_post_meta($post->ID, '_mh_guide_image_alt', true);
  $hero_image = guide_image((int) get_post_meta($post->ID, '_mh_guide_image', true), $alt, '(max-width: 1023px) 92vw, 600px', true);
  $facts      = guide_facts($post, $rendered);
  $cta        = guide_meta_json($post->ID, '_mh_guide_cta');
  $related    = guide_cards_html(guide_meta_json($post->ID, '_mh_guide_related'));
  $quote_url  = esc_url(home_url('/tarjouspyynto/kotimuutto/'));
  // with the calculator on this page the buttons jump to it, else to the price page's calculator
  $price_url  = guide_has_calculator($post) ? '#hintalaskuri' : esc_url(home_url('/kotimuutto/muuttopalvelun-hinta/#hintalaskuri'));
  $blog       = get_page_by_path('muuttovinkit');
  ?>
  <main id="content" class="mh-guide" role="main">
    <article>
      <header class="mh-guide-hero">
        <div class="mh-guide-wrap mh-guide-hero__grid">
          <div class="mh-guide-hero__text">
            <nav class="mh-guide-crumbs" aria-label="Murupolku">
              <?php if ($blog) : ?><a href="<?php echo esc_url(get_permalink($blog)); ?>">Muuttovinkit</a><span aria-hidden="true">/</span><?php endif; ?>Opas
            </nav>
            <h1 class="mh-guide-title"><?php echo $title; ?></h1>
            <?php if ($standfirst !== '') : ?><p class="mh-guide-standfirst"><?php echo esc_html($standfirst); ?></p><?php endif; ?>
            <?php if ($facts) : ?>
              <ul class="mh-guide-facts">
                <?php foreach ($facts as $fact) : ?>
                  <li><?php echo guide_icon((string) ($fact['icon'] ?? 'check')); ?><strong><?php echo esc_html((string) ($fact['value'] ?? '')); ?></strong><span><?php echo esc_html((string) ($fact['label'] ?? '')); ?></span></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
            <div class="mh-guide-actions">
              <a class="mh-painike mh-painike--yellow" href="<?php echo $price_url; ?>">Laske hinta-arvio</a>
              <a class="mh-painike mh-painike--black" href="<?php echo $quote_url; ?>">Pyydä tarjous</a>
            </div>
          </div>
          <?php if ($hero_image !== '') : ?>
            <figure class="mh-guide-hero__media"><?php echo $hero_image; ?></figure>
          <?php endif; ?>
        </div>
      </header>
      <?php $trust = guide_trust_html($post); ?>
      <?php if ($trust !== '') : ?>
        <div class="mh-guide-trust" aria-label="Luotettavuus">
          <div class="mh-guide-wrap"><?php echo $trust; ?></div>
        </div>
      <?php endif; ?>

      <?php
      $prev = 'white'; // the hero
      foreach ($sections as $section) {
        // The calculator: its own white section (its result panel is yellow), the markup of the price page inside .mh-landing
        // so it looks the same; the shortcode prints the heading and both columns.
        if (($section['kind'] ?? 'text') === 'calculator') {
          ?>
          <section class="mh-guide-calc" id="hintalaskuri">
            <div class="mh-guide-wrap mh-landing">
              <div class="mh-text-sec mh-guide-calc__grid"><?php echo $section['body']; ?></div>
            </div>
          </section>
          <?php
          $prev = 'white';
          continue;
        }
        $color   = $prev === 'yellow' ? 'white' : 'yellow';
        if (($section['kind'] ?? 'text') === 'toc') {
          $section['body'] = guide_toc_html($sections);
        }
        $classes = array_map('sanitize_html_class', $section['classes']);
        $wide    = in_array('sec-wide', $classes, true);
        $id      = $section['id'] !== '' ? $section['id'] : sanitize_title($section['title']);
        ?>
        <section class="mh-guide-sec mh-guide-sec--<?php echo $color; ?><?php echo $wide ? ' mh-guide-sec--wide' : ''; ?> <?php echo esc_attr(implode(' ', $classes)); ?>"<?php echo $id !== '' ? ' id="' . esc_attr($id) . '"' : ''; ?>>
          <div class="mh-guide-wrap mh-guide-sec__grid">
            <div class="mh-guide-sec__head">
              <?php if ($section['title'] !== '') : ?><h2><?php echo wptexturize($section['title']); ?></h2><?php endif; ?>
              <?php echo $section['lead']; ?>
            </div>
            <div class="mh-guide-sec__body"><?php echo $section['body']; ?></div>
          </div>
        </section>
        <?php
        $prev = $color;
      }

      $cta_color = $prev === 'yellow' ? 'white' : 'yellow';
      $cta_image = guide_image((int) ($cta['image'] ?? 0), (string) ($cta['alt'] ?? ''), '(max-width: 1023px) 92vw, 540px');
      ?>
      <section class="mh-guide-sec mh-guide-sec--<?php echo $cta_color; ?> mh-guide-cta" aria-labelledby="mh-guide-cta-title">
        <div class="mh-guide-wrap mh-guide-cta__grid">
          <div class="mh-guide-cta__text">
            <h2 id="mh-guide-cta-title"><?php echo esc_html((string) ($cta['title'] ?? 'Pyydä tarjous Muuttohaukoilta')); ?></h2>
            <p class="is-lead"><?php echo esc_html((string) ($cta['text'] ?? 'Kerro muuttosi tiedot, niin saat henkilökohtaisen tarjouksen. Alle 80 m² kodin muutolle hinnan saa heti Tilaamuutto.fi-palvelusta.')); ?></p>
            <div class="mh-guide-actions">
              <a class="mh-painike mh-painike--black" href="<?php echo $quote_url; ?>">Pyydä tarjous</a>
              <a class="mh-painike mh-painike--outline" href="<?php echo $price_url; ?>">Laske hinta-arvio</a>
            </div>
          </div>
          <?php if ($cta_image !== '') : ?><figure class="mh-guide-cta__media"><?php echo $cta_image; ?></figure><?php endif; ?>
        </div>
      </section>
      <?php if ($related !== '') : ?>
        <section class="mh-guide-sec mh-guide-sec--<?php echo $cta_color === 'yellow' ? 'white' : 'yellow'; ?> mh-guide-related" aria-labelledby="mh-guide-related-title">
          <div class="mh-guide-wrap">
            <div class="mh-guide-related__head"><h2 id="mh-guide-related-title">Lue myös</h2></div>
            <?php echo $related; ?>
          </div>
        </section>
      <?php endif; ?>
    </article>
    <?php echo guide_article_schema($post); ?>
  </main>
  <?php
}

get_footer();
