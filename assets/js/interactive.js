/**
 * Interactive sections (theme 1.2.25): the "Me vs muut" filter and the price
 * calculator. Markup and data come from inc/InteractiveSections.php; the
 * calculator reads its numbers from the JSON in `.mhi-data`, which the theme
 * builds from its own price table, so nothing is hard coded here.
 *
 * All sums are handled in cents (integers). Text drawn into the result comes
 * from the data or is calculated; what the visitor types never goes into HTML.
 */
(function () {
  'use strict';

  var DATA = null;
  var NBSP = String.fromCharCode(160);

  // ---------------------------------------------------------------- calculation
  const Laskenta = (function () {
    const NBSP = String.fromCharCode(160);   // kovo välilyönti
    const sent = (e) => Math.round(e * 100);

    function tuhat(n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, NBSP); }
    /** senttejä -> "1 075" tai "98,42" (desimaalit vain jos tarvitaan) */
    function eur(sentit) {
      const neg = sentit < 0;
      const c = Math.abs(Math.round(sentit));
      const e = Math.floor(c / 100), s = c % 100;
      return (neg ? '−' : '') + tuhat(e) + (s ? ',' + String(s).padStart(2, '0') : '');
    }
    /** [min, max] senttejä -> "450–600 €" (en-viiva, kovo välilyönti ennen €) */
    function alue(lo, hi) { return lo === hi ? eur(lo) + NBSP + '€' : eur(lo) + '–' + eur(hi) + NBSP + '€'; }
    function summa(lo, hi) { return alue(Math.floor(lo / 100) * 100, Math.ceil(hi / 100) * 100); }  // kokonaiset eurot, alue ei kutistu
    function nw(teksti) { return '<span class="mhi-nw">' + teksti + '</span>'; }   // pitää alueen samalla rivillä
    function monikko(n, yksikko, monta) { return n + NBSP + (n === 1 ? yksikko : monta); }

    function kokoluokka(m2) {
      const n = Math.round(m2), lk = DATA.hintaluokat;
      if (n < lk[0].min) return lk[0];                                   // alle 20 m²: ensimmäinen rivi
      return lk.find((k) => n >= k.min && (k.max === null || n <= k.max)) || lk[lk.length - 1];
    }
    function luokanNimi(k) { return k.max === null ? 'yli' + NBSP + (k.min - 1) + NBSP + 'm²' : k.min + '–' + k.max + NBSP + 'm²'; }

    /** täyden palvelun hinta: 2,15 x, pyöristys lähimpään 5 euroon (puolikas ylös) */
    function taysi(euroa) {
      const kerroinSadasosina = Math.round(DATA.taydenPalvelunKerroin * 100);   // 215
      return Math.round(euroa * kerroinSadasosina / (100 * DATA.pyoristysEuroa)) * DATA.pyoristysEuroa;
    }
    /** paketin hinta kokoluokalle, euroina [min, max] */
    function pakettihinta(luokka, paketti) {
      const lahde = paketti.laatikot ? luokka.laatikot : luokka.perus;
      return paketti.taysi ? lahde.map(taysi) : lahde.slice();
    }
    function kotitalousvahennys(euroa) {
      const s = DATA.siivous;
      return Math.min(s.vahennysKatto, Math.max(0, euroa * s.vahennysProsentti / 100 - s.omavastuu));
    }
    /** laatikoiden suositeltu määrä asunnon koon mukaan (Tilaamuutto.fi:n oma suositus) */
    function suositusLaatikot(m2) {
      const L = DATA.laatikkovuokra;
      const rivi = L.suositus.find((r) => m2 <= r.maxM2);
      return rivi ? rivi.laatikoita : Math.round(m2 * L.suositusKerroin);
    }
    /** vuokra kokonaisuudessaan koon mukaan: laatikot x päivät x päivähinta, toimitus ja nouto haarukkana (alle 12 km ... 12-30 km) */
    function laatikkovuokra(m2) {
      const L = DATA.laatikkovuokra;
      const maara = suositusLaatikot(m2), paivat = L.oletusPaivat;
      const suunnat = L.matkat.filter((x) => !x.laskematon && x.suuntaEuroa > 0).map((x) => sent(x.suuntaEuroa));
      const vuokra = maara * paivat * sent(L.paivaHintaEuroa);
      const toimitus = [L.suuntia * Math.min.apply(null, suunnat), L.suuntia * Math.max.apply(null, suunnat)];
      const suunta = [Math.min.apply(null, suunnat), Math.max.apply(null, suunnat)];
      return { maara, paivat, vuokra, toimitus, suunta, yhteensa: [vuokra + toimitus[0], vuokra + toimitus[1]] };
    }

    /**
     * t = { m2, paketti, siivous, piano, kaluste, varastointi, vuokra (true/false) }
     */
    function laske(t) {
      const m2 = Math.round(t.m2);
      const luokka = kokoluokka(m2);
      const paketti = DATA.paketit.find((p) => p.id === t.paketti);
      const hinta = pakettihinta(luokka, paketti).map(sent);

      // pienimmän hinnan paketti samalle koolle (vertailukohta), lasketaan datasta
      const vertailtavat = DATA.paketit.map((p) => ({ p, h: pakettihinta(luokka, p).map(sent) }));
      vertailtavat.sort((a, b) => (a.h[0] + a.h[1]) - (b.h[0] + b.h[1]));
      const vertailupaketti = vertailtavat[0];
      const erotus = vertailupaketti.p.id === paketti.id ? null : [hinta[0] - vertailupaketti.h[0], hinta[1] - vertailupaketti.h[1]];

      const mukana = [{
        id: 'paketti', nimi: 'Valittu paketti: ' + paketti.nimi, hinta,
        selite: [paketti.kuvaus + '. Kokoluokka ' + nw(luokanNimi(luokka)) + '.']
      }];
      const eiMukana = [];
      const huomiot = [];
      let siivousTiedot = null, vuokraTiedot = null;

      if (t.siivous) {
        const S = DATA.siivous;
        if (m2 <= S.hintaAlle) {
          const lo = S.hinta[0], hi = S.hinta[1];
          const v = [kotitalousvahennys(lo), kotitalousvahennys(hi)];
          const jalkeen = [lo - v[0], hi - v[1]];
          siivousTiedot = { hinta: [sent(lo), sent(hi)], vahennys: v.map(sent), jalkeen: jalkeen.map(sent) };
          mukana.push({
            id: 'siivous', nimi: 'Muuttosiivous', hinta: siivousTiedot.hinta,
            selite: [
              'Kotitalousvähennyksen jälkeen noin ' + nw(alue(siivousTiedot.jalkeen[0], siivousTiedot.jalkeen[1])) + ' (arvio).',
              'Vähennys on ' + S.vahennysProsentti + NBSP + '% työn hinnasta, josta vähennetään ' + S.omavastuu + NBSP + 'euron omavastuu (enintään ' + tuhat(S.vahennysKatto) + NBSP + '€ henkilöä kohden vuodessa), joten tällä hinnalla vähennys on ' + nw(alue(siivousTiedot.vahennys[0], siivousTiedot.vahennys[1])) + '.'
            ]
          });
        } else {
          eiMukana.push({ id: 'siivous', nimi: 'Muuttosiivous', teksti: S.iso, selite: S.isoSelite });
            }
      }
      if (t.piano)  eiMukana.push({ id: 'piano',  nimi: 'Piano tai kassakaappi', teksti: DATA.piano.teksti });
      if (t.kaluste) eiMukana.push({ id: 'kaluste', nimi: 'Kalusteasennus', teksti: DATA.kaluste.teksti });
      if (t.varastointi) eiMukana.push({ id: 'varastointi', nimi: 'Varastointi', teksti: DATA.varastointi.teksti });

      // Laatikoiden vuokraus erikseen: vain paketeille ilman laatikoita
      if (t.vuokra && !paketti.laatikot) {
        const v = laatikkovuokra(m2);
        vuokraTiedot = v;
        const L = DATA.laatikkovuokra;
        mukana.push({
          id: 'vuokra', nimi: 'Laatikoiden vuokraus', hinta: v.yhteensa,
          selite: [
            monikko(v.maara, 'laatikko', 'laatikkoa') + ' × ' + monikko(v.paivat, 'päivä', 'päivää') + ' × ' + eur(sent(L.paivaHintaEuroa)) + NBSP + '€' + NBSP + '=' + NBSP + eur(v.vuokra) + NBSP + '€.',
            'Toimitus ja nouto ' + L.suuntia + NBSP + '×' + NBSP + nw(alue(v.suunta[0], v.suunta[1])) + ' = ' + nw(alue(v.toimitus[0], v.toimitus[1])) + ', alle 30' + NBSP + 'km:n matkalla. Yli 30' + NBSP + 'km:n matkalta toimitus ja nouto laskutetaan tuntiveloituksella.'
          ]
        });
        huomiot.push('Laatikot toimitetaan ja noudetaan tiistaisin ja torstaisin. Laatikoiden määrä ja vuokra-aika on arvioitu asunnon koon mukaan.');
      }

      if (paketti.taysi) huomiot.push('Täyden palvelun muutto sisältää pakkauksen ja purkamisen. Hinnat ovat esimerkkihintoja asunnon koon mukaan, ja lopullinen hinta riippuu tavaramäärästä ja matkasta.');
      if (t.siivous) huomiot.push('Muuttosiivous tehdään vain siivouspaikkakuntien lähellä.');
      if (t.siivous || t.kaluste) huomiot.push('Kotitalousvähennys koskee muuttosiivousta ja uusien kalusteiden kokoonpanoa, kun se laskutetaan erikseen. Se ei koske muuttotyötä.');
      if (m2 > DATA.tilaaHetiEnintaan) huomiot.push('Tilaa heti -palvelusta voi tilata muuton, kun asunto on alle ' + (DATA.tilaaHetiEnintaan + 1) + NBSP + 'm². Suuremmista asunnoista pyydä tarjous.');

      let lo = 0, hi = 0;
      mukana.forEach((r) => { lo += r.hinta[0]; hi += r.hinta[1]; });

      return {
        m2, luokka, luokanNimi: luokanNimi(luokka), paketti, hinta,
        vertailupaketti: { paketti: vertailupaketti.p, hinta: vertailupaketti.h }, erotus,
        mukana, eiMukana, huomiot, yhteensa: [lo, hi],
        siivous: siivousTiedot, vuokra: vuokraTiedot,
        tilaaHeti: m2 <= DATA.tilaaHetiEnintaan
      };
    }

    return { laske, kokoluokka, luokanNimi, pakettihinta, taysi, kotitalousvahennys, laatikkovuokra, suositusLaatikot, eur, alue, summa, sent };
  })();

  // ------------------------------------------------------------ Me vs muut filter
  function initCompare(head) {
    var section = head.closest('.mh-text-sec') || document;
    var list = section.querySelector('[data-mhi-list]');
    if (!list) { return; }
    var boxes = Array.prototype.slice.call(head.querySelectorAll('input[name="mhi-aihe"]'));
    var rows = Array.prototype.slice.call(list.querySelectorAll('.mhi-compare__row'));
    var count = head.querySelector('[data-mhi-count]');
    var reset = head.querySelector('[data-mhi-reset]');

    function update() {
      var picked = boxes.filter(function (b) { return b.checked; }).map(function (b) { return b.value; });
      var visible = 0;
      rows.forEach(function (row) {
        var show = picked.length === 0 || picked.indexOf(row.getAttribute('data-topic')) !== -1;
        row.hidden = !show;
        if (show) { visible++; }
      });
      count.textContent = 'Näytetään ' + visible + ' / ' + rows.length + ' asiaa';
      reset.setAttribute('data-hidden', picked.length === 0 ? 'true' : 'false');
      reset.tabIndex = picked.length === 0 ? -1 : 0;
    }

    boxes.forEach(function (b) { b.addEventListener('change', update); });
    reset.addEventListener('click', function () {
      boxes.forEach(function (b) { b.checked = false; });
      update();
      if (boxes[0]) { boxes[0].focus(); }
    });
    update();
  }

  // --------------------------------------------------------------- price calculator
  function initCalculator(head) {
    var dataTag = head.querySelector('.mhi-data');
    var section = head.closest('.mh-text-sec') || document;
    var result = section.querySelector('[data-mhi-result]');
    if (!dataTag || !result) { return; }
    try { DATA = JSON.parse(dataTag.textContent); } catch (e) { return; }

    var $ = function (id) { return document.getElementById(id); };
    var el = {
      koko: $('mhi-koko'), liuku: $('mhi-koko-liuku'), vihje: $('mhi-koko-vihje'),
      siivous: $('mhi-lisa-siivous'), piano: $('mhi-lisa-piano'), kaluste: $('mhi-lisa-kaluste'), varastointi: $('mhi-lisa-varastointi'),
      vuokraRyhma: head.querySelector('[data-mhi-rent-group]'), vuokra: $('mhi-vuokra'), vuokraTeksti: head.querySelector('[data-mhi-rent-note]'),
      tulos: result.querySelector('[data-mhi-panel]'), tilaa: result.querySelector('[data-mhi-order]'), tarjous: result.querySelector('[data-mhi-quote]'),
      palkki: result.querySelector('[data-mhi-bar]')
    };
    if (el.palkki) {
      el.palkkiTeksti = el.palkki.querySelector('[data-mhi-bar-label]');
      el.palkkiHinta = el.palkki.querySelector('[data-mhi-bar-price]');
      el.palkkiTarjous = el.palkki.querySelector('[data-mhi-bar-quote]');
    }
    var K = DATA.koko;
    var m2 = K.oletus;
    var previous = '';

    function readInt(input, min, max) {
      var v = Math.round(parseFloat(String(input.value).replace(',', '.')));
      if (!isFinite(v) || v < min || v > max) { return null; }
      return v;
    }
    function clamp(v, min, max) { return Math.min(max, Math.max(min, v)); }
    function selectedPackage() {
      var checked = head.querySelector('input[name="mhi-paketti"]:checked');
      return DATA.paketit.filter(function (p) { return p.id === checked.value; })[0];
    }

    function setSize(v) {
      m2 = v;
      el.koko.value = v;
      el.liuku.value = v;
      el.liuku.style.setProperty('--mhi-fill', ((v - K.min) / (K.max - K.min) * 100) + '%');
      el.liuku.setAttribute('aria-valuetext', v + ' neliömetriä');
    }

    function readState() {
      var p = selectedPackage();
      return {
        m2: m2, paketti: p.id,
        siivous: el.siivous.checked, piano: el.piano.checked, kaluste: el.kaluste.checked, varastointi: el.varastointi.checked,
        vuokra: el.vuokra.checked && !p.laatikot,
        laatikoita: Laskenta.suositusLaatikot(m2),
        paivia: DATA.laatikkovuokra.oletusPaivat
      };
    }

    function draw(r) {
      var L = Laskenta;
      var h = '<div class="mhi-result__head">';
      h += '<p class="mhi-result__top">' + r.paketti.nimi + ', ' + r.m2 + NBSP + 'm²</p>';
      h += '<p class="mhi-result__price"><span class="mhi-vh">Hinta-arvio </span><span class="mhi-nw">' + L.summa(r.yhteensa[0], r.yhteensa[1]) + '</span></p>';

      h += '<p class="mhi-result__note">Summa sisältää vain hinnoitellut rivit.</p>';
      if (r.erotus) {
        h += '<p class="mhi-result__change"><strong class="mhi-result__delta">+' + NBSP + '<span class="mhi-nw">' + L.alue(r.erotus[0], r.erotus[1]) + '</span> ' + r.paketti.erotusSana + '</strong><span class="mhi-result__vs">verrattuna samankokoisen asunnon pakettiin ' + r.vertailupaketti.paketti.nimi + ' (<span class="mhi-nw">' + L.alue(r.vertailupaketti.hinta[0], r.vertailupaketti.hinta[1]) + '</span>).</span></p>';
      } else {
        h += '<p class="mhi-result__change"><span class="mhi-result__vs">Muita paketteja verrataan tähän hintaan samalle asunnon koolle.</span></p>';
      }
      h += '</div>';

      h += '<div class="mhi-result__group"><p class="mhi-label">Mukana summassa</p><dl class="mhi-lines">';
      r.mukana.forEach(function (rivi) {
        var kicker = /^(Valittu paketti): (.*)$/.exec(rivi.nimi);
        var nimi = kicker ? '<span class="mhi-lines__kicker">' + kicker[1] + '</span>' + kicker[2] : rivi.nimi;
        h += '<div class="mhi-lines__row"><dt>' + nimi + '</dt><dd class="mhi-lines__sum"><span class="mhi-nw">' + L.alue(rivi.hinta[0], rivi.hinta[1]) + '</span></dd>';
        rivi.selite.forEach(function (s) { h += '<dd class="mhi-lines__note">' + s + '</dd>'; });
        h += '</div>';
      });
      h += '</dl></div>';

      if (r.eiMukana.length) {
        h += '<div class="mhi-result__group"><p class="mhi-label">Ei mukana summassa</p><dl class="mhi-lines">';
        r.eiMukana.forEach(function (rivi) {
          h += '<div class="mhi-lines__row"><dt>' + rivi.nimi + '</dt><dd class="mhi-lines__text">' + rivi.teksti + '</dd>';
          if (rivi.selite) { h += '<dd class="mhi-lines__note">' + rivi.selite + '</dd>'; }
          h += '</div>';
        });
        h += '</dl></div>';
      }

      if (r.huomiot.length) {
        h += '<div class="mhi-result__group"><p class="mhi-label">Huomioitavaa</p><ul class="mhi-remarks">';
        r.huomiot.forEach(function (n) { h += '<li>' + n + '</li>'; });
        h += '</ul></div>';
      }
      return h;
    }

    function update() {
      var p = selectedPackage();
      el.vuokraRyhma.hidden = p.laatikot;        // separate box rental only for packages without boxes
      var boxes = Laskenta.suositusLaatikot(m2), days = DATA.laatikkovuokra.oletusPaivat;
      el.vuokraTeksti.textContent = 'Arvio asunnon koon mukaan: ' + boxes + NBSP + 'laatikkoa ' + days + NBSP + 'päivän ajaksi.';

      var r = Laskenta.laske(readState());
      var html = draw(r);
      if (html !== previous) { el.tulos.innerHTML = html; previous = html; }
      el.tilaa.hidden = !r.tilaaHeti;
      el.tarjous.setAttribute('href', quoteLink(r));
      if (el.palkki) {
        el.palkkiTeksti.textContent = 'Hinta-arvio, ' + r.m2 + NBSP + 'm²';
        el.palkkiHinta.textContent = Laskenta.summa(r.yhteensa[0], r.yhteensa[1]);
        el.palkkiTarjous.setAttribute('href', quoteLink(r));
      }
    }

    // The floating price: on phones and tablets (the stacked layout) it shows while the calculator is on screen
    // and its own result is not, so the price never needs a scroll to be seen.
    function watchBar() {
      if (!el.palkki) { return; }
      var stacked = window.matchMedia('(max-width: 1024px)');
      var queued = false;
      function place() {
        queued = false;
        var vh = window.innerHeight;
        var s = (section.getBoundingClientRect ? section : el.tulos).getBoundingClientRect();
        var p = el.tulos.getBoundingClientRect();
        var inView = s.top < vh - 200 && s.bottom > 0;   // the controls are properly on screen, not just peeking in
        var resultSeen = p.top < vh - 160 && p.bottom > 160;      // the top of the result, with the price, is on screen
        var show = stacked.matches && inView && !resultSeen;
        el.palkki.hidden = !show;
        document.body.classList.toggle('mhi-bar-on', show);
      }
      function queue() { if (!queued) { queued = true; window.requestAnimationFrame(place); } }
      window.addEventListener('scroll', queue, { passive: true });
      window.addEventListener('resize', queue);
      queue();
    }

    // "Pyydä tarjous" opens the quote form with the choices already filled in (see client.js)
    function quoteLink(r) {
      var s = readState();
      var p = new URLSearchParams();
      p.set('pa', r.m2);
      p.set('pkt', r.paketti.id);
      var extras = ['siivous', 'piano', 'kaluste', 'varastointi'].filter(function (k) { return s[k]; });
      if (extras.length) { p.set('lisa', extras.join(',')); }
      if (r.paketti.laatikot || s.vuokra) { p.set('laatikot', s.laatikoita); }
      if (s.vuokra) { p.set('paivat', s.paivia); }
      p.set('arvio', Math.floor(r.yhteensa[0] / 100) + '-' + Math.ceil(r.yhteensa[1] / 100));
      return DATA.linkit.tarjous + (DATA.linkit.tarjous.indexOf('?') === -1 ? '?' : '&') + p.toString();
    }

    el.koko.addEventListener('input', function () {
      var v = readInt(el.koko, K.min, K.max);
      if (v === null) { el.vihje.hidden = false; return; }   // a half typed value: keep the previous result
      el.vihje.hidden = true;
      setSize(v);
      update();
    });
    el.koko.addEventListener('change', function () {         // leaving the field: clamp to the allowed range
      var raw = Math.round(parseFloat(String(el.koko.value).replace(',', '.')));
      setSize(isFinite(raw) ? clamp(raw, K.min, K.max) : m2);
      el.vihje.hidden = true;
      update();
    });
    el.liuku.addEventListener('input', function () {
      el.vihje.hidden = true;
      setSize(parseInt(el.liuku.value, 10));
      update();
    });

    head.querySelectorAll('input[name="mhi-paketti"], input[name="mhi-lisa"], #mhi-vuokra').forEach(function (e) { e.addEventListener('change', update); });

    el.tilaa.setAttribute('href', DATA.linkit.tilaaHeti);
    setSize(K.oletus);
    update();
    watchBar();
  }

  function start() {
    var compare = document.querySelector('[data-mhi="vertailu"]');
    var calculator = document.querySelector('[data-mhi="laskuri"]');
    if (compare) { initCompare(compare); }
    if (calculator) { initCalculator(calculator); }
  }

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', start); } else { start(); }
})();
