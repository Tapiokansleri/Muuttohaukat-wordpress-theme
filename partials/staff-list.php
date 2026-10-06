<?php
/**
 * People list (staff_render() in inc/Staff.php; outside template-parts/, which the theme loads on every request). The markup of the old
 * Beaver Builder Henkilöstö module, so assets/css/henkilosto.css
 * keeps styling it; no Beaver Builder classes needed.
 *
 * @var object $settings heading, heading_tag, columns, persons
 * @var object $module   node
 */
use Muuttohaukat\StaffHelpers;

$heading     = isset($settings->heading) ? trim((string) $settings->heading) : '';
$heading_tag = in_array($settings->heading_tag ?? 'h2', ['h2', 'h3', 'h4'], true) ? $settings->heading_tag : 'h2';
$columns     = max(1, min(3, absint($settings->columns ?? 2)));
$persons     = array_values(array_filter((array) ($settings->persons ?? []), fn($p) => is_object($p) && (trim((string) ($p->name ?? '')) !== '' || !empty($p->photo))));
$lang_labels = StaffHelpers::language_labels();

if ($heading === '' && !$persons) {
  return;
}
?>

<div class="mh-henkilosto" data-module-id="<?php echo esc_attr($module->node); ?>">
  <?php if ($heading !== '') : ?>
    <<?php echo tag_escape($heading_tag); ?> class="mh-henkilosto__heading"><?php echo esc_html($heading); ?></<?php echo tag_escape($heading_tag); ?>>
  <?php endif; ?>

  <?php if ($persons) : ?>
    <ul class="mh-henkilosto__grid" style="--mh-henkilosto-columns: <?php echo esc_attr((string) $columns); ?>;">
      <?php foreach ($persons as $person) :
        $name             = trim((string) ($person->name ?? ''));
        $role             = trim((string) ($person->role ?? ''));
        $responsibilities = trim((string) ($person->responsibilities ?? ''));
        $phone            = trim((string) ($person->phone ?? ''));
        $email            = trim((string) ($person->email ?? ''));
        $photo_id         = is_numeric($person->photo ?? '') ? absint($person->photo) : 0;
        $languages        = StaffHelpers::normalize_languages($person->languages ?? []);
        $phone_href       = StaffHelpers::phone_href($phone);
        $email_href       = StaffHelpers::email_href($email);
        $lang_names       = array_values(array_filter(array_map(fn($c) => $lang_labels[$c] ?? '', $languages)));
        $lang_aria        = $lang_names ? 'Kielet: ' . implode(', ', $lang_names) : '';
        ?>
        <li class="mh-henkilosto__person">
          <div class="mh-henkilosto__media">
            <?php if ($photo_id) : ?>
              <?php echo wp_get_attachment_image($photo_id, 'medium_large', false, ['class' => 'mh-henkilosto__photo', 'loading' => 'lazy', 'alt' => $name]); ?>
            <?php else : ?>
              <span class="mh-henkilosto__photo mh-henkilosto__photo--placeholder" aria-hidden="true"></span>
            <?php endif; ?>
          </div>

          <div class="mh-henkilosto__info">
            <div class="mh-henkilosto__name-row">
              <?php if ($name !== '') : ?>
                <p class="mh-henkilosto__name"><?php echo esc_html($name); ?></p>
              <?php endif; ?>
              <?php if ($languages) : ?>
                <ul class="mh-henkilosto__flags"<?php echo $lang_aria ? ' aria-label="' . esc_attr($lang_aria) . '"' : ''; ?>>
                  <?php foreach ($languages as $code) :
                    $svg = StaffHelpers::language_flag_svg($code);
                    if (!$svg) continue;
                    $label = $lang_labels[$code] ?? $code;
                    ?>
                    <li class="mh-henkilosto__flag" title="<?php echo esc_attr($label); ?>">
                      <span class="mh-henkilosto__flag-svg"><?php echo $svg; // trusted inline SVG ?></span>
                      <span class="screen-reader-text"><?php echo esc_html($label); ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>

            <?php if ($role !== '') : ?>
              <p class="mh-henkilosto__role"><?php echo esc_html($role); ?></p>
            <?php endif; ?>
            <?php if ($responsibilities !== '') : ?>
              <p class="mh-henkilosto__focus"><?php echo esc_html($responsibilities); ?></p>
            <?php endif; ?>

            <?php if ($phone !== '') : ?>
              <p class="mh-henkilosto__phone">
                <span class="mh-henkilosto__icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                <?php if ($phone_href) : ?><a href="<?php echo esc_attr($phone_href); ?>"><?php echo esc_html($phone); ?></a><?php else : ?><span><?php echo esc_html($phone); ?></span><?php endif; ?>
              </p>
            <?php endif; ?>

            <?php if ($email !== '') : ?>
              <p class="mh-henkilosto__email">
                <span class="mh-henkilosto__icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
                <?php if ($email_href) : ?><a href="<?php echo esc_attr($email_href); ?>"><?php echo esc_html(antispambot($email)); ?></a><?php else : ?><span><?php echo esc_html($email); ?></span><?php endif; ?>
              </p>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
