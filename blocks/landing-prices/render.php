<?php
/** @var array $attributes @var string $content */
$rows       = \Muuttohaukat\price_rows();
$show_basic = !empty($attributes['showBasic']);
$quote_url  = home_url('/tarjouspyynto/');
$price_url  = home_url('/kotimuutto/muuttopalvelun-hinta/');
$wrapper = \Muuttohaukat\landing_block_wrapper_attributes($attributes, ['mh-landing-section', 'mh-landing-prices'], [
  'id' => $attributes['anchor'] ?? 'hinnat',
]);

$cell = function ($value) use ($quote_url) {
  return $value === null || $value === ''
    ? '<a href="' . esc_url($quote_url) . '">Pyydä tarjous</a>'
    : esc_html($value);
};
?>
<section <?php echo $wrapper; ?>>
  <div class="mh-landing__inner">
    <?php echo $content; ?>

    <div class="mh-landing-prices__table-wrap">
      <table class="mh-landing-prices__table">
        <thead>
          <tr>
            <th scope="col">Asunnon koko</th>
            <?php if ($show_basic) : ?><th scope="col">Muuttopalvelu</th><?php endif; ?>
            <th scope="col">Muutto ja laatikot</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row) : ?>
            <tr>
              <th scope="row">
                <?php echo esc_html($row['size']); ?>
                <?php if (!empty($row['type'])) : ?>
                  <span class="mh-landing-prices__type"><?php echo esc_html($row['type']); ?></span>
                <?php endif; ?>
              </th>
              <?php if ($show_basic) : ?><td><?php echo $cell($row['basic'] ?? null); ?></td><?php endif; ?>
              <td><?php echo $cell($row['boxes'] ?? null); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (!empty($attributes['note'])) : ?>
      <p class="mh-landing-prices__note">
        Hinta määräytyy asunnon koon, tavaramäärän ja matkan mukaan. Tarkemmat hinnat ja se, mitä niihin sisältyy, ovat
        <a href="<?php echo esc_url($price_url); ?>">muuttopalvelun hinta -sivulla</a>.
      </p>
    <?php endif; ?>
  </div>
</section>
