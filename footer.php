<?php
/**
 * The footer template. Included by get_footer().
 *
 * @package Muuttohaukat
 */
namespace Muuttohaukat;

$app = app();
$footer = $app->getOption('footer');

$footer['copyrightText'] = !empty($footer['copyrightText']) ? $footer['copyrightText'] : '&copy; ' . date('Y') . ' ' . get_bloginfo('name');
$footer['tagline']       = !empty($footer['tagline']) ? $footer['tagline'] : '';

$phone = $app->getOption('footer_phone', false) ?: '010 400 4500';
$email = $app->getOption('footer_email', false) ?: 'muuttohaukat@muuttohaukat.com';

$widget_columns = ['footer-1', 'footer-2', 'footer-3', 'footer-4'];
$acf_menus      = !empty($footer['menus']) && is_array($footer['menus']) ? array_values($footer['menus']) : [];

$footer_slots = [];
foreach ($widget_columns as $index => $sidebar_id) {
  $has_widget = is_active_sidebar($sidebar_id);
  $acf_menu   = $acf_menus[$index] ?? null;
  $has_acf    = !empty($acf_menu['menu']) && is_array($acf_menu['menu']);

  if ($has_widget || $has_acf) {
    $footer_slots[] = [
      'sidebar_id' => $sidebar_id,
      'acf_menu'   => $has_widget ? null : $acf_menu,
      'use_widget' => $has_widget,
    ];
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

    <?php if (!empty($footer_slots)) : ?>
      <div class="site-footer__widgets" data-columns="<?= count($footer_slots) ?>">
        <div class="site-footer__widgets-inner">
          <?php foreach ($footer_slots as $slot) : ?>
            <div class="site-footer__column">
              <?php if ($slot['use_widget']) : ?>
                <?php dynamic_sidebar($slot['sidebar_id']); ?>
              <?php else : ?>
                <?php renderFooterAcfMenuColumn($slot['acf_menu']); ?>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="site-footer__bottom">
      <div class="site-footer__bottom-inner">
        <p class="site-footer__copyright">
          <?= wp_kses_post($footer['copyrightText']) ?>
          <?php if (!empty($footer['tagline'])) : ?>
            <span class="site-footer__tagline"> — <?= esc_html($footer['tagline']) ?></span>
          <?php endif; ?>
        </p>
        <p class="site-footer__sitemap">
          <a href="<?= esc_url(home_url('/sivukartta')) ?>"><?= esc_html__('Sivukartta', 'muuttohaukat') ?></a>
        </p>
      </div>
    </div>

  </footer>
</div><!-- #page -->

  <?php wp_footer(); ?>
  </body>
</html>
