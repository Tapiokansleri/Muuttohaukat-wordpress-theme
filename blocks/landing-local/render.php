<?php
/** @var array $attributes @var string $content */
$office = \Muuttohaukat\office($attributes['office'] ?? '');
$city   = trim((string) ($attributes['city'] ?? ''));
$hours  = \Muuttohaukat\office_hours();
// The office's people sit in the left column under the text (Tapio 22.9.2026:
// "lisää yhteyshenkilöt vasempaan palstaan ei alle").
$staff  = $office ? \Muuttohaukat\office_staff_html($office) : '';
$wrapper = \Muuttohaukat\landing_block_wrapper_attributes($attributes, ['mh-landing-section', 'mh-landing-local'], [
  'id' => $attributes['anchor'] ?? 'paikallisesti',
]);
?>
<section <?php echo $wrapper; ?>>
  <div class="mh-landing__inner mh-landing-local__inner">
    <div class="mh-landing-local__text"><?php echo $content; ?><?php echo $staff; ?></div>

    <?php if ($office) : ?>
      <aside class="mh-landing-local__office">
        <p class="mh-landing-kicker"><?php echo esc_html($office['label']); ?></p>
        <p class="mh-landing-local__address">
          <?php echo esc_html($office['street']); ?><br>
          <?php echo esc_html($office['zip'] . ' ' . $office['city']); ?>
        </p>
        <p class="mh-landing-local__phone">
          <a href="tel:<?php echo esc_attr(preg_replace('/\s+/', '', $office['phone'])); ?>"><?php echo esc_html($office['phone']); ?></a>
        </p>
        <p class="mh-landing-local__hours"><?php echo esc_html($hours['text']); ?></p>
        <?php if (!empty($attributes['map'])) : ?>
          <?php echo \Muuttohaukat\office_map_html($office); ?>
          <p class="mh-landing-local__route"><a href="<?php echo esc_url(\Muuttohaukat\office_map_link($office)); ?>" target="_blank" rel="noopener">Reittiohjeet Google Mapsissa</a></p>
        <?php endif; ?>
        <?php // No link to the area page on the area page itself.
        if (untrailingslashit(wp_make_link_relative(get_permalink())) !== untrailingslashit($office['page'])) : ?>
          <p class="mh-landing-local__link">
            <a class="mh-landing__button mh-landing__button--ghost" href="<?php echo esc_url(home_url($office['page'])); ?>">
              <?php echo esc_html($office['area']); ?>
            </a>
          </p>
        <?php endif; ?>
      </aside>
    <?php endif; ?>
  </div>

  <?php if ($office && !empty($attributes['schema'])) : ?>
    <script type="application/ld+json"><?php
      echo wp_json_encode(\Muuttohaukat\office_schema($office, $city), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?></script>
  <?php endif; ?>
</section>
