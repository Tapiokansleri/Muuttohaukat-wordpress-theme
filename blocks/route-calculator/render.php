<?php
/**
 * Route calculator block — server-side rendered.
 *
 * Renders the AJAX form + preset Paimio routes grid. Calls
 * \Muuttohaukat\landing_calculate_route() for each preset destination.
 *
 * @package Muuttohaukat
 */

$route_origin = 'Paimio';
$route_destinations = [
  'Turku', 'Kaarina', 'Sauvo', 'Salo', 'Naantali', 'Loimaa',
  'Forssa', 'Helsinki', 'Espoo', 'Hämeenlinna', 'Pori', 'Tampere',
];

$rest_url = rest_url('muuttohaukat/v1/route');
?>
<div class="mh-route-calculator">
  <form class="mh-landing-route-form" id="mh-route-form" data-rest-url="<?= esc_attr($rest_url); ?>">
    <label class="mh-landing-route-form__field">
      <span class="mh-landing-route-form__label"><?= esc_html__('Mistä', 'muuttohaukat'); ?></span>
      <input type="text" name="from" value="<?= esc_attr($route_origin); ?>" placeholder="<?= esc_attr__('Esim. Paimio', 'muuttohaukat'); ?>" required>
    </label>
    <label class="mh-landing-route-form__field">
      <span class="mh-landing-route-form__label"><?= esc_html__('Mihin', 'muuttohaukat'); ?></span>
      <input type="text" name="to" placeholder="<?= esc_attr__('Esim. Helsinki', 'muuttohaukat'); ?>" required>
    </label>
    <button type="submit" class="mh-landing__button mh-landing__button--primary"><?= esc_html__('Laske reitti', 'muuttohaukat'); ?></button>
    <div class="mh-landing-route-form__result" aria-live="polite"></div>
  </form>

  <div class="mh-landing-routes">
    <?php foreach ($route_destinations as $to_name) :
      $calc = function_exists('\\Muuttohaukat\\landing_calculate_route')
        ? \Muuttohaukat\landing_calculate_route($route_origin, $to_name)
        : null;
    ?>
      <div class="mh-landing-route">
        <div class="mh-landing-route__name"><?= esc_html($route_origin . ' – ' . $to_name); ?></div>
        <?php if ($calc) : ?>
          <div class="mh-landing-route__meta"><b><?= esc_html($calc['km']); ?> km</b> · n. <?= esc_html($calc['time']); ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
(function () {
  var form = document.getElementById('mh-route-form');
  if (!form || form.dataset.mhBound) return;
  form.dataset.mhBound = '1';
  var result = form.querySelector('.mh-landing-route-form__result');
  var restUrl = form.dataset.restUrl;

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var from = form.elements.from.value.trim();
    var to   = form.elements.to.value.trim();
    if (!from || !to) return;

    result.innerHTML = '<p class="mh-landing-route-form__loading"><?= esc_js(__('Lasketaan reittiä...', 'muuttohaukat')); ?></p>';

    var url = restUrl + '?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to);
    fetch(url)
      .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
      .then(function (res) {
        if (!res.ok || !res.data || !res.data.km) {
          result.innerHTML = '<p class="mh-landing-route-form__error"><?= esc_js(__('Reittiä ei voitu laskea. Tarkista kaupunkien nimet.', 'muuttohaukat')); ?></p>';
          return;
        }
        var d = res.data;
        result.innerHTML =
          '<div class="mh-landing-route mh-landing-route--custom">' +
            '<div class="mh-landing-route__name">' + escapeHtml(d.from) + ' – ' + escapeHtml(d.to) + '</div>' +
            '<div class="mh-landing-route__meta"><b>' + d.km + ' km</b> &middot; n. ' + escapeHtml(d.time) + '</div>' +
          '</div>';
      })
      .catch(function () {
        result.innerHTML = '<p class="mh-landing-route-form__error"><?= esc_js(__('Virhe haussa. Yritä uudelleen.', 'muuttohaukat')); ?></p>';
      });
  });

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[c];
    });
  }
})();
</script>
