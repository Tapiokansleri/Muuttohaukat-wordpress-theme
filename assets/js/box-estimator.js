/**
 * Box estimator of the guide article template ([laatikkoarvio], inc/Guide.php).
 *
 * Numbers come from the price calculator's data (laatikkovuokra), passed in the data-est attribute, and the recommendation is the
 * same function as suositusLaatikot() in interactive.js: the first table row whose maxM2 is not below the size, else size x factor
 * rounded. Money is counted in cents.
 *
 * Measurement (Google Tag Manager reads the dataLayer; no trigger exists until one is built in the container):
 *   box_estimator_start       first choice made (once); est_control: size | more | days | trip
 *   box_estimator_result      the choices have settled for 1.5 s and the result changed (max 10)
 *   box_estimator_cta_click   a link of the result was clicked; est_cta: order | cardboard
 * result and click events carry est_m2, est_boxes, est_more (0/1), est_days and est_trip.
 */
(function () {
  'use strict';

  var root = document.querySelector('.mh-est[data-est]');
  if (!root) { return; }
  var D;
  try { D = JSON.parse(root.getAttribute('data-est')); } catch (e) { return; }

  var NBSP = ' ';
  var el = {
    num: root.querySelector('#mh-est-m2'),
    range: root.querySelector('#mh-est-range'),
    hint: root.querySelector('#mh-est-hint'),
    more: root.querySelectorAll('input[name="mh-est-more"]'),
    days: root.querySelector('#mh-est-days'),
    trip: root.querySelector('#mh-est-trip'),
    result: root.querySelector('.mh-est__result')
  };
  var current = null;
  var lowShown = false;
  var lastHtml = '';

  function recommend(m2) {
    for (var i = 0; i < D.rows.length; i += 1) {
      if (m2 <= D.rows[i].maxM2) { return D.rows[i].boxes; }
    }
    return Math.round(m2 * D.factor);
  }
  function roundUp(n, step) { return Math.ceil(n / step - 1e-9) * step; }
  function eur(cents) {
    var whole = cents % 100 === 0;
    var text = whole ? String(cents / 100) : (cents / 100).toFixed(2).replace('.', ',');
    return text + NBSP + '€';
  }
  function tripById(id) {
    for (var i = 0; i < D.trips.length; i += 1) { if (D.trips[i].id === id) { return D.trips[i]; } }
    return D.trips[0];
  }
  function moreChecked() {
    for (var i = 0; i < el.more.length; i += 1) { if (el.more[i].checked) { return el.more[i].value === '1'; } }
    return false;
  }
  function plural(n, one, many) { return n === 1 ? one : many; }

  function sizeValue() {
    var v = parseInt(el.num.value, 10);
    return isNaN(v) ? null : v;
  }

  function compute() {
    var m2 = sizeValue();
    if (m2 === null || m2 < D.min || m2 > D.max) { return null; }
    var base = recommend(m2);
    var more = moreChecked();
    var boxes = more ? roundUp(base * D.more, D.round) : base;
    var days = parseInt(el.days.value, 10) || D.days;
    var trip = tripById(el.trip.value);
    var rent = boxes * days * D.rentCents;
    var travel = trip.cents === null ? null : trip.cents * D.directions;
    return { m2: m2, base: base, boxes: boxes, more: more, days: days, trip: trip, rent: rent, travel: travel,
      total: travel === null ? null : rent + travel, spare: days * D.rentCents };
  }

  function render() {
    var r = compute();
    var v = sizeValue();
    var over = v !== null && v > D.max;
    var low = v === null || v < D.min;
    // a too small number is only called out when the field is left (typing "6" on the way to "62" is not an error)
    el.hint.hidden = !(over || (low && lowShown));
    if (!el.hint.hidden) {
      el.hint.textContent = over
        ? 'Yli ' + D.max + ' m² asunnoista pyydä tarjous: arvio laaditaan kohteen mukaan.'
        : 'Syötä asunnon koko väliltä ' + D.min + '–' + D.max + ' m².';
    }
    if (!r) { return; }
    current = r;
    var rule = r.m2 > D.rows[D.rows.length - 1].maxM2
      ? 'Yli ' + D.rows[D.rows.length - 1].maxM2 + NBSP + 'm² asunnolle suositus on ' + String(D.factor).replace('.', ',') + ' laatikkoa neliötä kohden.'
      : 'Tilaamuutto.fi:n suositus ' + r.m2 + NBSP + 'm² asunnolle.';
    var sub = r.more
      ? 'Perussuositus ' + r.base + ' laatikkoa, ja siihen on lisätty ' + Math.round((D.more - 1) * 100) + NBSP + '% tavallista suuremman tavaramäärän vuoksi. Lisäys on Muuttohaukkojen oma arvio.'
      : rule;
    var travelLine = r.travel === null
      ? 'Yli 30' + NBSP + 'km matkoilla toimitus laskutetaan tuntiveloituksella, pyydä tarjous.'
      : (r.travel === 0 ? 'Noudat ja palautat laatikot itse: 0' + NBSP + '€.'
        : D.directions + NBSP + '×' + NBSP + eur(r.trip.cents) + NBSP + '=' + NBSP + eur(r.travel));
    var html = '<p class="mh-est__big"><strong>' + r.boxes + '</strong><span>' + plural(r.boxes, 'laatikko', 'laatikkoa') + '</span></p>'
      + '<p class="mh-est__sub">' + sub + '</p>'
      + '<dl class="mh-est__sum">'
      + '<div><dt>Vuokra ' + r.days + NBSP + 'päivää</dt><dd>' + r.boxes + NBSP + '×' + NBSP + r.days + NBSP + '×' + NBSP + eur(D.rentCents) + NBSP + '=' + NBSP + eur(r.rent) + '</dd></div>'
      + '<div><dt>Toimitus ja nouto</dt><dd>' + travelLine + '</dd></div>'
      + (r.total === null ? '' : '<div class="is-total"><dt>Yhteensä</dt><dd>' + eur(r.total) + '</dd></div>')
      + '</dl>'
      + '<p class="mh-est__spare">Yksi varalaatikko maksaa ' + r.days + NBSP + 'päivältä ' + eur(r.spare) + '. Hinnat ovat laatikkosivun hintoja: Tilaamuutto.fi:n tilauksessa sitova hinta näkyy ennen tilausta. Pahvilaatikot jäävät sinulle, ja niiden hinta pyydetään tarjouksena.</p>'
      + '<div class="mh-est__actions"><a class="mh-painike mh-painike--black" href="' + D.order + '" data-est-cta="order">Tilaa laatikot</a>'
      + '<a class="mh-est__link" href="' + D.boxPage + '" data-est-cta="cardboard">Pahvilaatikot ja tarvikkeet</a></div>';
    // the result is replaced only when it changed: a blur that rebuilds a link under the mouse would swallow the click
    if (html !== lastHtml) { lastHtml = html; el.result.innerHTML = html; }
  }

  /* ---------- measurement ---------- */
  var sent = {}, last = '', count = 0, timer = null;
  function push(name, data) {
    var msg = { event: name }, k;
    for (k in data) { if (Object.prototype.hasOwnProperty.call(data, k)) { msg[k] = data[k]; } }
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push(msg);
  }
  function snapshot() {
    var r = current;
    if (!r) { return { est_m2: 0, est_boxes: 0, est_more: 0, est_days: 0, est_trip: 'none' }; }
    return { est_m2: r.m2, est_boxes: r.boxes, est_more: r.more ? 1 : 0, est_days: r.days, est_trip: r.trip.id };
  }
  function settle() {
    window.clearTimeout(timer);
    timer = null;
    var data = snapshot(), key = JSON.stringify(data);
    if (!current || key === last || count >= 10) { return; }
    last = key;
    count += 1;
    push('box_estimator_result', data);
  }
  function touched(control) {
    if (!sent.start) { sent.start = true; push('box_estimator_start', { est_control: control }); }
    window.clearTimeout(timer);
    timer = window.setTimeout(settle, 1500);
  }

  /* ---------- events ---------- */
  function setSize(value, from) {
    var v = parseInt(value, 10);
    if (!isNaN(v) && v >= D.min && v <= D.max) { el.range.value = v; }
    if (from === 'range') { el.num.value = el.range.value; }
    render();
    touched('size');
  }
  el.num.addEventListener('input', function () { setSize(el.num.value, 'num'); });
  el.num.addEventListener('focus', function () { lowShown = false; });
  el.num.addEventListener('blur', function () {
    lowShown = true;
    var v = sizeValue();
    if (v !== null && v < D.min) { el.num.value = D.min; setSize(D.min, 'num'); } else { render(); }
  });
  el.range.addEventListener('input', function () { setSize(el.range.value, 'range'); });
  Array.prototype.forEach.call(el.more, function (input) {
    input.addEventListener('change', function () { render(); touched('more'); });
  });
  el.days.addEventListener('change', function () { render(); touched('days'); });
  el.trip.addEventListener('change', function () { render(); touched('trip'); });
  root.addEventListener('click', function (event) {
    var a = event.target && event.target.closest ? event.target.closest('a[data-est-cta]') : null;
    if (!a) { return; }
    if (timer !== null) { settle(); }
    var data = snapshot();
    data.est_cta = a.getAttribute('data-est-cta');
    push('box_estimator_cta_click', data);
  });

  render();
}());
