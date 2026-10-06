<?php
/**
 * The footer template. Included by get_footer().
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$app = app();
$footer = $app->getOption('footer');

$footer['copyrightText'] = !empty($footer['copyrightText']) ? $footer['copyrightText'] : '&copy; ' . date('Y') . ' ' . get_bloginfo('name');
$footer['tagline']       = !empty($footer['tagline']) ? $footer['tagline'] : '';

$phone = $app->getOption('footer_phone', false) ?: '010 400 4500';
$email = $app->getOption('footer_email', false) ?: 'muuttohaukat@muuttohaukat.com';

$widget_columns = ['footer-1', 'footer-2', 'footer-3', 'footer-4'];

$footer_slots = [];
foreach ($widget_columns as $sidebar_id) {
  if (is_active_sidebar($sidebar_id)) {
    $footer_slots[] = $sidebar_id;
  }
}
?>

  </main>
  <footer class="site-footer">

    <div class="site-footer__contact">
      <div class="site-footer__contact-inner">
        <a href="tel:<?= esc_attr(preg_replace('/\s+/', '', $phone)) ?>" class="site-footer__contact-item">
          <span class="site-footer__contact-label"><?= esc_html__('Soita', 'muuttohaukat') ?></span>
          <span class="site-footer__contact-value"><?= esc_html($phone) ?></span>
        </a>
        <a href="mailto:<?= esc_attr($email) ?>" class="site-footer__contact-item">
          <span class="site-footer__contact-label"><?= esc_html__('Lähetä sähköpostia', 'muuttohaukat') ?></span>
          <span class="site-footer__contact-value"><?= esc_html($email) ?></span>
        </a>
      </div>
    </div>

    <?php \Muuttohaukat\Breadcrumbs\render(); ?>

    <?php if (!empty($footer_slots)) : ?>
      <div class="site-footer__widgets" data-columns="<?= count($footer_slots) ?>">
        <div class="site-footer__widgets-inner">
          <?php foreach ($footer_slots as $sidebar_id) : ?>
            <div class="site-footer__column">
              <?php dynamic_sidebar($sidebar_id); ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php
    $eeat_facts = [
      [
        'value' => '1992',
        'label' => __( 'Perheyritys vuodesta', 'muuttohaukat' ),
        'url'   => home_url( '/yritystiedot/' ),
      ],
      [
        'value' => '100 000+',
        'label' => __( 'muuttoa', 'muuttohaukat' ),
        'url'   => home_url( '/muuttohaukat-ylitti-100-000-muuton-rajapyykin/' ),
      ],
      [
        'value' => '4',
        'label' => __( 'toimipistettä', 'muuttohaukat' ),
        'url'   => home_url( '/yhteystiedot/' ),
      ],
      [
        'value' => '100+',
        'label' => __( 'työntekijää', 'muuttohaukat' ),
        'url'   => home_url( '/rekry/' ),
      ],
    ];
    ?>
    <div class="site-footer__eeat">
      <div class="site-footer__eeat-inner">
        <p class="site-footer__eeat-heading screen-reader-text"><?php esc_html_e( 'Muuttohaukat lyhyesti', 'muuttohaukat' ); ?></p>
        <ul class="site-footer__eeat-list">
          <?php foreach ( $eeat_facts as $fact ) : ?>
            <li class="site-footer__eeat-item">
              <a class="site-footer__eeat-link" href="<?php echo esc_url( $fact['url'] ); ?>">
                <span class="site-footer__eeat-value"><?php echo esc_html( $fact['value'] ); ?></span>
                <span class="site-footer__eeat-label"><?php echo esc_html( $fact['label'] ); ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="site-footer__eeat-claim">
          <a href="<?php echo esc_url( home_url( '/yritysmuutto/' ) ); ?>">
            <?php esc_html_e( 'Yksi Suomen suurimmista koti- ja yritysmuuttojen tekijöistä', 'muuttohaukat' ); ?>
          </a>
        </p>
      </div>
    </div>

    <div class="site-footer__bottom">
      <div class="site-footer__bottom-inner">
        <p class="site-footer__copyright">
          <?= wp_kses_post($footer['copyrightText']) ?>
          <?php if (!empty($footer['tagline'])) : ?>
            <span class="site-footer__tagline"> — <?= esc_html($footer['tagline']) ?></span>
          <?php endif; ?>
        </p>
        <p class="site-footer__sitemap">
          <a href="<?= esc_url(home_url('/sivukartta/')) ?>"><?= esc_html__('Sivukartta', 'muuttohaukat') ?></a>
        </p>
        <?php if (function_exists('\Muuttohaukat\company_facts')) :
          $mh_facts = \Muuttohaukat\company_facts();
          $mh_offices = array_map(fn($o) => $o['city'] . ', ' . $o['street'], \Muuttohaukat\offices());
          ?>
          <p class="site-footer__company">
            <a href="<?= esc_url(home_url('/yritystiedot/')) ?>"><?= esc_html($mh_facts['legal_name']) ?></a><?= esc_html(' · Y-tunnus ' . $mh_facts['business_id'] . ' · ' . implode(' · ', $mh_offices)) ?>
          </p>
        <?php endif; ?>
      </div>
    </div>

  </footer>
</div><!-- #page -->

  <?php wp_footer(); ?>
  </body>
</html>
