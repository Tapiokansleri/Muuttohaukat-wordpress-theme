/**
 * Box estimator of the guide article template ([laatikkoarvio], inc/Guide.php).
 *
 * Numbers come from the price calculator's data (laatikkovuokra), passed in the data-est attribute, and the recommendation is the
 * same function as suositusLaatikot() in interactive.js: the first table row whose maxM2 is not below the size, else size x factor
 * rounded. Money is counted in cents.
 *
 * Delivery: a visitor cannot know the distance to an office, so the question is the municipality (a type-ahead like the home
 * calculator's city fields, data from assets/data/kunnat.json, plus the Swedish names) or "I pick them up myself". The tier is
 * computed here: the straight line from the municipality centre to the NEAREST office (the theme's offices() list) times the road
 * factor, in whole kilometres: under nearKm 2 x nearCents, nearKm to farKm 2 x midCents, over farKm hourly (shown, not summed).
 * It is an estimate and the result says which office was used.
 *
 * Measurement (Google Tag Manager reads the dataLayer; no trigger exists until one is built in the container):
 *   box_estimator_start       first choice made (once); est_control: size | more | days | city | pickup
 *   box_estimator_result      the choices have settled for 1.5 s and the result changed (max 10)
 *   box_estimator_cta_click   a link of the result was clicked; est_cta: order | cardboard
 * result and click events carry est_m2, est_boxes, est_more (0/1), est_days and est_trip (near | mid | far | pickup | none); the
 * municipality name is never sent.
 */
(function () {
  'use strict';

  var root = document.querySelector('.mh-est[data-est]');
  if (!root) { return; }
  var D;
  try { D = JSON.parse(root.getAttribute('data-est')); } catch (e) { return; }
  var C = { cities: [], popular: [] };
  try { C = JSON.parse(document.getElementById('mh-est-cities').textContent); } catch (e) { /* the field stays a plain text field */ }

  var NBSP = ' ';
  var el = {
    num: root.querySelector('#mh-est-m2'),
    range: root.querySelector('#mh-est-range'),
    hint: root.querySelector('#mh-est-hint'),
    more: root.querySelectorAll('input[name="mh-est-more"]'),
    days: root.querySelector('#mh-est-days'),
    city: root.querySelector('#mh-est-city'),
    list: root.querySelector('#mh-est-city-list'),
    cityHint: root.querySelector('#mh-est-city-hint'),
    pickup: root.querySelector('#mh-est-pickup'),
    result: root.querySelector('.mh-est__result')
  };
  var current = null;
  var lowShown = false;
  var lastHtml = '';
  var cityBlurred = false;

  /* ---------- the recommendation and the money ---------- */
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
  function moreChecked() {
    for (var i = 0; i < el.more.length; i += 1) { if (el.more[i].checked) { return el.more[i].value === '1'; } }
    return false;
  }
  function plural(n, one, many) { return n === 1 ? one : many; }
  function sizeValue() {
    var v = parseInt(el.num.value, 10);
    return isNaN(v) ? null : v;
  }

  /* ---------- municipalities and the nearest office ---------- */
  var cityIndex = null;
  function key(text) { return String(text).trim().toLocaleLowerCase('fi').normalize('NFD').replace(/[̀-ͯ]/g, ''); }
  function findCity(text) {
    if (!cityIndex) {
      cityIndex = {};
      C.cities.forEach(function (c) { cityIndex[key(c[0])] = c; });
    }
    return cityIndex[key(text)] || null;
  }
  function km(a, b) {
    var rad = Math.PI / 180;
    var dLat = (b[1] - a[1]) * rad, dLon = (b[2] - a[2]) * rad;
    var h = Math.pow(Math.sin(dLat / 2), 2) + Math.cos(a[1] * rad) * Math.cos(b[1] * rad) * Math.pow(Math.sin(dLon / 2), 2);
    return Math.round(2 * 6371.0088 * Math.asin(Math.sqrt(h)) * D.delivery.road);
  }
  /** the delivery as the visitor has told it: { state: none | pickup | near | mid | far, office, km, city, cents } */
  function delivery() {
    if (el.pickup.checked) { return { state: 'pickup', cents: 0 }; }
    var city = el.city.value.trim() === '' ? null : findCity(el.city.value);
    if (!city) { return { state: 'none', cents: null, unknown: el.city.value.trim() !== '' }; }
    var best = null;
    D.offices.forEach(function (o) {
      var d = km(city, [o.name, o.lat, o.lng]);
      if (best === null || d < best.km) { best = { km: d, office: o.name }; }
    });
    var state = best.km < D.delivery.nearKm ? 'near' : (best.km <= D.delivery.farKm ? 'mid' : 'far');
    return { state: state, office: best.office, km: best.km, city: city[3] || city[0],
      cents: state === 'near' ? D.delivery.nearCents : (state === 'mid' ? D.delivery.midCents : null) };
  }

  function compute() {
    var m2 = sizeValue();
    if (m2 === null || m2 < D.min || m2 > D.max) { return null; }
    var base = recommend(m2);
    var more = moreChecked();
    var boxes = more ? roundUp(base * D.more, D.round) : base;
    var days = parseInt(el.days.value, 10) || D.days;
    var d = delivery();
    var rent = boxes * days * D.rentCents;
    var travel = d.cents === null ? null : d.cents * D.directions;
    return { m2: m2, base: base, boxes: boxes, more: more, days: days, d: d, rent: rent, travel: travel,
      total: rent + (travel || 0), complete: travel !== null, spare: days * D.rentCents };
  }

  function travelLine(r) {
    var d = r.d, near = D.delivery.nearKm, far = D.delivery.farKm;
    if (d.state === 'pickup') { return 'Noudat ja palautat laatikot itse: 0' + NBSP + '€.'; }
    if (d.state === 'none') { return 'Valitse kunta, niin näet toimituksen arvion.'; }
    var tier = d.state === 'near' ? 'alle ' + near + NBSP + 'km' : (d.state === 'mid' ? near + '–' + far + NBSP + 'km' : 'yli ' + far + NBSP + 'km');
    var note = 'Arvio, lähin toimipiste ' + d.office + ', ' + tier + '.';
    if (d.state === 'far') { return 'Yli ' + far + NBSP + 'km: toimitus laskutetaan tunneittain, pyydä tarjous. ' + note; }
    return D.directions + NBSP + '×' + NBSP + eur(d.cents) + NBSP + '=' + NBSP + eur(r.travel) + '. ' + note;
  }

  function render() {
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
    var typed = el.city.value.trim() !== '' && !el.pickup.checked;
    var unknown = typed && !findCity(el.city.value);
    el.cityHint.hidden = !(unknown && cityBlurred);
    if (!el.cityHint.hidden) { el.cityHint.textContent = 'Valitse kunta ehdotuksista.'; }
    el.city.disabled = el.pickup.checked;
    var r = compute();
    if (!r) { return; }
    current = r;
    var rule = r.m2 > D.rows[D.rows.length - 1].maxM2
      ? 'Yli ' + D.rows[D.rows.length - 1].maxM2 + NBSP + 'm² asunnolle suositus on ' + String(D.factor).replace('.', ',') + ' laatikkoa neliötä kohden.'
      : 'Tilaamuutto.fi:n suositus ' + r.m2 + NBSP + 'm² asunnolle.';
    var sub = r.more
      ? 'Perussuositus ' + r.base + ' laatikkoa, ja siihen on lisätty ' + Math.round((D.more - 1) * 100) + NBSP + '% tavallista suuremman tavaramäärän vuoksi. Lisäys on Muuttohaukkojen oma arvio.'
      : rule;
    var totalLabel = r.complete ? 'Yhteensä' : 'Yhteensä ilman toimitusta';
    var html = '<p class="mh-est__big"><strong>' + r.boxes + '</strong><span>' + plural(r.boxes, 'laatikko', 'laatikkoa') + '</span></p>'
      + '<p class="mh-est__sub">' + sub + '</p>'
      + '<dl class="mh-est__sum">'
      + '<div><dt>Vuokra ' + r.days + NBSP + 'päivää</dt><dd>' + r.boxes + NBSP + '×' + NBSP + r.days + NBSP + '×' + NBSP + eur(D.rentCents) + NBSP + '=' + NBSP + eur(r.rent) + '</dd></div>'
      + '<div><dt>Toimitus ja nouto</dt><dd>' + travelLine(r) + '</dd></div>'
      + '<div class="is-total"><dt>' + totalLabel + '</dt><dd>' + eur(r.total) + '</dd></div>'
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
    return { est_m2: r.m2, est_boxes: r.boxes, est_more: r.more ? 1 : 0, est_days: r.days, est_trip: r.d.state };
  }
  function settle() {
    window.clearTimeout(timer);
    timer = null;
    var data = snapshot(), k = JSON.stringify(data);
    if (!current || k === last || count >= 10) { return; }
    last = k;
    count += 1;
    push('box_estimator_result', data);
  }
  function touched(control) {
    if (!sent.start) { sent.start = true; push('box_estimator_start', { est_control: control }); }
    window.clearTimeout(timer);
    timer = window.setTimeout(settle, 1500);
  }

  /* ---------- the municipality suggestions: "tam" lists Tampere; an empty field lists every municipality, the big cities first ---------- */
  function suggestions(text) {
    var q = key(text), hits = [];
    C.cities.forEach(function (c) {
      var alias = c.length > 3;                              // a Swedish name: found and listed after the Finnish ones
      var rank = C.popular.indexOf(c[3] || c[0]);
      if (q === '') { hits.push({ name: c[0], at: -1, alias: alias, group: alias ? 2 : (rank === -1 ? 1 : 0), rank: rank === -1 ? 999 : rank }); return; }
      var at = key(c[0]).indexOf(q);
      if (at === -1 || (at > 0 && q.length < 2)) { return; }
      hits.push({ name: c[0], at: at, alias: alias, group: at === 0 ? 0 : 1, rank: rank === -1 ? 999 : rank });
    });
    hits.sort(function (a, b) { return a.group - b.group || (a.alias ? 1 : 0) - (b.alias ? 1 : 0) || a.rank - b.rank || a.name.localeCompare(b.name, 'fi'); });
    return hits;
  }
  (function attachSuggest() {
    if (!C.cities.length || !el.list) { return; }
    var items = [], active = -1;
    function setActive(n) {
      active = n;
      Array.prototype.forEach.call(el.list.children, function (li, i) {
        li.classList.toggle('is-active', i === n);
        li.setAttribute('aria-selected', i === n ? 'true' : 'false');
      });
      if (n >= 0) {
        el.city.setAttribute('aria-activedescendant', el.list.children[n].id);
        var li = el.list.children[n];
        if (li.offsetTop < el.list.scrollTop) { el.list.scrollTop = li.offsetTop; }
        else if (li.offsetTop + li.offsetHeight > el.list.scrollTop + el.list.clientHeight) { el.list.scrollTop = li.offsetTop + li.offsetHeight - el.list.clientHeight; }
      } else { el.city.removeAttribute('aria-activedescendant'); }
    }
    function close() {
      el.list.hidden = true;
      el.city.setAttribute('aria-expanded', 'false');
      setActive(-1);
    }
    function choose(name) {
      el.city.value = name;
      close();
      render();
      touched('city');
    }
    function open() {
      var q = key(el.city.value);
      items = suggestions(el.city.value);
      if (!items.length || (items.length === 1 && key(items[0].name) === q)) { close(); return; }   // nothing to add
      el.list.textContent = '';
      items.forEach(function (hit, n) {
        var li = document.createElement('li');
        li.id = 'mh-est-city-o' + n;
        li.className = 'mh-est__suggest-item';
        li.setAttribute('role', 'option');
        if (hit.at >= 0 && q !== '') {                       // the matching part in bold
          li.appendChild(document.createTextNode(hit.name.slice(0, hit.at)));
          var bold = document.createElement('strong');
          bold.textContent = hit.name.slice(hit.at, hit.at + q.length);
          li.appendChild(bold);
          li.appendChild(document.createTextNode(hit.name.slice(hit.at + q.length)));
        } else { li.textContent = hit.name; }
        li.addEventListener('mousedown', function (e) { e.preventDefault(); });   // the field keeps its focus
        li.addEventListener('click', function () { choose(hit.name); });
        el.list.appendChild(li);
      });
      el.list.hidden = false;
      el.city.setAttribute('aria-expanded', 'true');
      setActive(-1);
    }
    el.city.addEventListener('input', function () { cityBlurred = false; open(); render(); touched('city'); });
    el.city.addEventListener('focus', function () { cityBlurred = false; open(); });
    el.city.addEventListener('blur', function () { close(); cityBlurred = true; render(); });
    el.city.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        if (el.list.hidden) { open(); }
        if (!items.length) { return; }
        e.preventDefault();
        var n = active + (e.key === 'ArrowDown' ? 1 : -1);
        setActive(n < 0 ? items.length - 1 : (n >= items.length ? 0 : n));
      } else if (e.key === 'Enter') {
        if (!el.list.hidden && active >= 0) { e.preventDefault(); choose(items[active].name); }
        else { e.preventDefault(); }
      } else if (e.key === 'Escape') { close(); }
    });
  }());

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
  el.pickup.addEventListener('change', function () { render(); touched('pickup'); });
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
