<?php
/**
 * Sticky mobile CTA — rendered outside <main> by the landing template.
 *
 * @package Muuttohaukat
 */

$phone_raw = '010 000 0000';
$phone_tel = preg_replace('/\s+/', '', $phone_raw);
$quote_url = '#tarjous';
?>
<aside class="mh-landing-sticky-cta" aria-label="<?= esc_attr__('Pikayhteydet', 'muuttohaukat'); ?>">
  <a class="mh-landing-sticky-cta__btn mh-landing-sticky-cta__btn--phone" href="tel:<?= esc_attr($phone_tel); ?>"><span><?= esc_html__('Soita', 'muuttohaukat'); ?></span></a>
  <a class="mh-landing-sticky-cta__btn mh-landing-sticky-cta__btn--quote" href="<?= esc_url($quote_url); ?>"><span><?= esc_html__('Pyydä tarjous', 'muuttohaukat'); ?></span></a>
</aside>
