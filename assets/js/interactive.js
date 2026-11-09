/**
 * Interactive section (theme 1.2.25): the price calculator. Markup and data come from inc/InteractiveSections.php; the
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
    function nw(teksti) { return '<span class="mhi-nw">' + teksti + '</span>'; }
    /** link to an internal page (the addresses come from the theme), or plain text if the page is not known */
    function linkki(avain, teksti) {
      const u = (DATA.linkit.sivut || {})[avain];
      return u ? '<a href="' + String(u).replace(/"/g, '%22') + '">' + teksti + '</a>' : teksti;
    }   // pitää alueen samalla rivillä
    function monikko(n, yksikko, monta) { return n + NBSP + (n === 1 ? yksikko : monta); }

    function kokoluokka(m2) {
      const n = Math.round(m2), lk = DATA.hintaluokat;
      if (n < lk[0].min) return lk[0];                                   // alle 20 m²: ensimmäinen rivi
      return lk.find((k) => n >= k.min && (k.max === null || n <= k.max)) || lk[lk.length - 1];
    }
    function luokanNimi(k) { return k.max === null ? 'yli' + NBSP + (k.min - 1) + NBSP + 'm²' : k.min + '–' + k.max + NBSP + 'm²'; }
    function esc(teksti) { return String(teksti).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }

    // "Muutan toiseen kaupunkiin": a municipality by name (also without ä and ö), and the estimated road distance
    // between two of them: the straight line between their centre points times the road factor (see the theme's
    // INTERACTIVE_ROAD_FACTOR), in whole kilometres.
    let kaupunkiHaku = null;
    function avain(teksti) { return String(teksti).trim().toLocaleLowerCase('fi').normalize('NFD').replace(/[̀-ͯ]/g, ''); }
    function kaupunki(teksti) {
      if (!kaupunkiHaku) {
        kaupunkiHaku = {};
        DATA.kaupunkimatka.kaupungit.forEach((k) => { kaupunkiHaku[avain(k[0])] = k; });
      }
      return kaupunkiHaku[avain(teksti)] || null;
    }
    function kaupunkienMatka(a, b) {
      const rad = Math.PI / 180;
      const dLat = (b[1] - a[1]) * rad, dLon = (b[2] - a[2]) * rad;
      const h = Math.sin(dLat / 2) ** 2 + Math.cos(a[1] * rad) * Math.cos(b[1] * rad) * Math.sin(dLon / 2) ** 2;
      return Math.round(2 * 6371.0088 * Math.asin(Math.sqrt(h)) * DATA.kaupunkimatka.tieKerroin);
    }
    /** what the two city fields say: { tila: ok | puuttuu | tuntematon | sama, lahto, kohde, km } */
    function siirto(lahtoTeksti, kohdeTeksti) {
      const t1 = String(lahtoTeksti).trim(), t2 = String(kohdeTeksti).trim();
      const a = t1 ? kaupunki(t1) : null, b = t2 ? kaupunki(t2) : null;
      if ((t1 && !a) || (t2 && !b)) { return { tila: 'tuntematon' }; }
      if (!a || !b) { return { tila: 'puuttuu' }; }
      if (a === b) { return { tila: 'sama', lahto: a[0], kohde: b[0], km: 0 }; }
      return { tila: 'ok', lahto: a[0], kohde: b[0], km: kaupunkienMatka(a, b) };
    }

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

      const mukana = [{
        id: 'paketti', nimi: 'Valittu paketti: ' + paketti.nimi, linkki: paketti.taysi ? 'taysi' : 'kotimuutto', hinta,
        selite: [paketti.kuvaus + '. Kokoluokka ' + nw(luokanNimi(luokka)) + '.']
      }];
      const eiMukana = [];
      const huomiot = [];
      let siivousTiedot = null, vuokraTiedot = null, siirtoTiedot = null;

      // Between two cities more than rajaKm apart: euroaKm for every kilometre between them (the whole distance)
      if (t.siirto) {
        const S = t.siirto, C = DATA.kaupunkimatka;
        if (S.tila === 'ok' && S.km > C.rajaKm) {
          const euroa = Math.round(S.km * C.euroaKm);
          siirtoTiedot = { lahto: S.lahto, kohde: S.kohde, km: S.km, euroa };
          mukana.push({
            id: 'siirto', nimi: 'Muutto toiseen kaupunkiin', hinta: [sent(euroa), sent(euroa)],
            selite: [esc(S.lahto) + ' → ' + esc(S.kohde) + ', noin ' + S.km + NBSP + 'km × ' + eur(sent(C.euroaKm)) + NBSP + '€/km. Matka on arvio.']
          });
        } else if (S.tila === 'ok' || S.tila === 'sama') {
          huomiot.push('Matkalisä lasketaan vain yli ' + C.rajaKm + NBSP + 'km matkoille.');
        } else {
          huomiot.push('Valitse lähtö- ja kohdekaupunki, niin matkalisä lasketaan arvioon.');
        }
      }

      if (t.siivous) {
        const S = DATA.siivous;
        if (m2 <= S.hintaAlle) {
          const lo = S.hinta[0], hi = S.hinta[1];
          const v = [kotitalousvahennys(lo), kotitalousvahennys(hi)];
          const jalkeen = [lo - v[0], hi - v[1]];
          siivousTiedot = { hinta: [sent(lo), sent(hi)], vahennys: v.map(sent), jalkeen: jalkeen.map(sent) };
          mukana.push({
            id: 'siivous', nimi: 'Muuttosiivous', linkki: 'siivous', hinta: siivousTiedot.hinta,
            selite: [
              'Kotitalousvähennyksen jälkeen noin ' + nw(alue(siivousTiedot.jalkeen[0], siivousTiedot.jalkeen[1])) + ' (arvio).'
            ]
          });
        } else {
          eiMukana.push({ id: 'siivous', nimi: 'Muuttosiivous', linkki: 'siivous', teksti: S.iso, selite: S.isoSelite });
            }
      }
      if (t.piano)  eiMukana.push({ id: 'piano',  nimi: 'Piano tai kassakaappi', linkki: 'piano', teksti: DATA.piano.teksti });
      if (t.kaluste) eiMukana.push({ id: 'kaluste', nimi: 'Kalusteasennus', linkki: 'kaluste', teksti: DATA.kaluste.teksti });
      if (t.varastointi) eiMukana.push({ id: 'varastointi', nimi: 'Varastointi', teksti: DATA.varastointi.teksti.replace('Pelican Self Storagen', linkki('varastointi', 'Pelican Self Storagen')) });

      // Laatikoiden vuokraus erikseen: vain paketeille ilman laatikoita
      if (t.vuokra && !paketti.laatikot) {
        const v = laatikkovuokra(m2);
        vuokraTiedot = v;
        const L = DATA.laatikkovuokra;
        mukana.push({
          id: 'vuokra', nimi: 'Laatikoiden vuokraus', linkki: 'laatikot', hinta: v.yhteensa,
          selite: [
            monikko(v.maara, 'laatikko', 'laatikkoa') + ' × ' + monikko(v.paivat, 'päivä', 'päivää') + ' × ' + eur(sent(L.paivaHintaEuroa)) + NBSP + '€' + NBSP + '=' + NBSP + eur(v.vuokra) + NBSP + '€.',
            'Toimitus ja nouto ' + L.suuntia + NBSP + '×' + NBSP + nw(alue(v.suunta[0], v.suunta[1])) + ' = ' + nw(alue(v.toimitus[0], v.toimitus[1])) + ', alle 30' + NBSP + 'km:n matkalla. Yli 30' + NBSP + 'km:n matkalta toimitus ja nouto laskutetaan tuntiveloituksella.'
          ]
        });
        huomiot.push('Laatikot toimitetaan ja noudetaan tiistaisin ja torstaisin. Laatikoiden määrä ja vuokra-aika on arvioitu asunnon koon mukaan.');
      }

      if (paketti.taysi) huomiot.push('Täyden palvelun muutto sisältää pakkauksen ja purkamisen. Hinnat ovat esimerkkihintoja asunnon koon mukaan, ja lopullinen hinta riippuu tavaramäärästä ja matkasta.');
      if (t.siivous) huomiot.push('Muuttosiivous tehdään vain siivouspaikkakuntien lähellä.');
      if (t.siivous || t.kaluste) huomiot.push(linkki('ehdot', 'Kotitalousvähennys') + ' koskee muuttosiivousta ja uusien kalusteiden kokoonpanoa, kun se laskutetaan erikseen. Se ei koske muuttotyötä.');
      if (m2 > DATA.tilaaHetiEnintaan) huomiot.push('<a href="' + DATA.linkit.tilaaHeti + '" target="_blank" rel="noopener noreferrer">Tilaamuutto.fi</a>-palvelussa voi tilata muuton, kun asunto on alle ' + (DATA.tilaaHetiEnintaan + 1) + NBSP + 'm². Suuremmista asunnoista pyydä tarjous.');

      let lo = 0, hi = 0;
      mukana.forEach((r) => { lo += r.hinta[0]; hi += r.hinta[1]; });

      return {
        m2, luokka, luokanNimi: luokanNimi(luokka), paketti, hinta,
        mukana, eiMukana, huomiot, yhteensa: [lo, hi],
        siivous: siivousTiedot, vuokra: vuokraTiedot, siirto: siirtoTiedot,
        tilaaHeti: m2 <= DATA.tilaaHetiEnintaan
      };
    }

    return { laske, siirto, kokoluokka, luokanNimi, pakettihinta, taysi, kotitalousvahennys, laatikkovuokra, suositusLaatikot, linkki, eur, alue, summa, sent };
  })();

  // --------------------------------------------------------------- price calculator
  function initCalculator(head) {
    var dataTag = head.querySelector('.mhi-data');
    var section = head.closest('.mh-text-sec') || document;
    var result = section.querySelector('[data-mhi-result]');
    if (!dataTag || !result) { return; }
    try { DATA = JSON.parse(dataTag.textContent); } catch (e) { return; }

    var $ = function (id) { return document.getElementById(id); };
    var el = {
      koko: $('mhi-koko'), liuku: $('mhi-koko-liuku'), vihje: $('mhi-koko-vihje'), raja: $('mhi-koko-raja'),
      siivous: $('mhi-lisa-siivous'), piano: $('mhi-lisa-piano'), kaluste: $('mhi-lisa-kaluste'), varastointi: $('mhi-lisa-varastointi'),
      vuokraRyhma: head.querySelector('[data-mhi-rent-group]'), vuokra: $('mhi-vuokra'), vuokraTeksti: head.querySelector('[data-mhi-rent-note]'),
      tulos: result.querySelector('[data-mhi-panel]'), tilaa: result.querySelector('[data-mhi-order]'), tarjous: result.querySelector('[data-mhi-quote]'),
      palkki: result.querySelector('[data-mhi-bar]'),
      kaupunki: $('mhi-kaupunki'), kaupunkiKentat: head.querySelector('[data-mhi-city-fields]'), lahto: $('mhi-lahto'), kohde: $('mhi-kohde'),
      kaupunkiVihje: head.querySelector('[data-mhi-city-note]'), kaupungit: $('mhi-kaupungit')
    };
    var hasCity = !!(el.kaupunki && el.kaupunkiKentat && el.lahto && el.kohde && el.kaupunkiVihje && el.kaupungit && DATA.kaupunkimatka);
    if (el.palkki) {
      el.palkkiTeksti = el.palkki.querySelector('[data-mhi-bar-label]');
      el.palkkiHinta = el.palkki.querySelector('[data-mhi-bar-price]');
      el.palkkiTarjous = el.palkki.querySelector('[data-mhi-bar-quote]');
    }
    var K = DATA.koko;
    var m2 = K.oletus;
    var previous = '';
    var current = null;                          // the result on screen, for the measurement below

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
      el.raja.hidden = v < K.max;                // the end of the slider: bigger homes get a quote
    }

    function readState() {
      var p = selectedPackage();
      return {
        m2: m2, paketti: p.id,
        siivous: el.siivous.checked, piano: el.piano.checked, kaluste: el.kaluste.checked, varastointi: el.varastointi.checked,
        vuokra: el.vuokra.checked && !p.laatikot,
        laatikoita: Laskenta.suositusLaatikot(m2),
        paivia: DATA.laatikkovuokra.oletusPaivat,
        siirto: hasCity && el.kaupunki.checked ? Laskenta.siirto(el.lahto.value, el.kohde.value) : null
      };
    }

    /** the line under the two city fields */
    function cityNote(S, r) {
      if (!S) { return ''; }
      if (S.tila === 'tuntematon') { return 'Valitse kaupunki ehdotuksista.'; }
      if (S.tila === 'puuttuu') { return 'Valitse sekä lähtö- että kohdekaupunki.'; }
      if (S.tila === 'sama') { return 'Lähtö- ja kohdekaupunki ovat samat.'; }
      if (r.siirto) { return 'Matka noin ' + S.km + NBSP + 'km, matkalisä ' + Laskenta.eur(Laskenta.sent(r.siirto.euroa)) + NBSP + '€.'; }
      return 'Matka noin ' + S.km + NBSP + 'km. Matkalisä lasketaan vain yli ' + DATA.kaupunkimatka.rajaKm + NBSP + 'km matkoille.';
    }
    function fillCities() {
      if (el.kaupungit.children.length) { return; }
      var options = document.createDocumentFragment();
      DATA.kaupunkimatka.kaupungit.forEach(function (k) {
        var option = document.createElement('option');
        option.value = k[0];
        options.appendChild(option);
      });
      el.kaupungit.appendChild(options);
    }

    function draw(r) {
      var L = Laskenta;
      var h = '<div class="mhi-result__head">';
      h += '<p class="mhi-result__top">' + r.paketti.nimi + ', ' + r.m2 + NBSP + 'm²</p>';
      h += '<p class="mhi-result__price"><span class="mhi-vh">Hinta-arvio </span><span class="mhi-nw">' + L.summa(r.yhteensa[0], r.yhteensa[1]) + '</span></p>';

      h += '</div>';

      h += '<div class="mhi-result__group"><dl class="mhi-lines">';
      r.mukana.forEach(function (rivi) {
        var kicker = /^(Valittu paketti): (.*)$/.exec(rivi.nimi);
        var nimiTeksti = kicker ? kicker[2] : rivi.nimi;
        var linkitetty = rivi.linkki ? L.linkki(rivi.linkki, nimiTeksti) : nimiTeksti;
        var nimi = kicker ? '<span class="mhi-lines__kicker">' + kicker[1] + '</span>' + linkitetty : linkitetty;
        h += '<div class="mhi-lines__row"><dt>' + nimi + '</dt><dd class="mhi-lines__sum"><span class="mhi-nw">' + L.alue(rivi.hinta[0], rivi.hinta[1]) + '</span></dd>';
        rivi.selite.forEach(function (s) { h += '<dd class="mhi-lines__note">' + s + '</dd>'; });
        h += '</div>';
      });
      h += '</dl></div>';

      if (r.eiMukana.length) {
        h += '<div class="mhi-result__group"><p class="mhi-label">Ei mukana summassa</p><dl class="mhi-lines">';
        r.eiMukana.forEach(function (rivi) {
          h += '<div class="mhi-lines__row"><dt>' + (rivi.linkki ? L.linkki(rivi.linkki, rivi.nimi) : rivi.nimi) + '</dt><dd class="mhi-lines__text">' + rivi.teksti + '</dd>';
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

      var state = readState();
      var r = Laskenta.laske(state);
      current = r;
      if (hasCity) { el.kaupunkiVihje.textContent = cityNote(state.siirto, r); }
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
    function quoteLink(r) { return quoteLinkFor(r, readState()); }
    function quoteLinkFor(r, s) {
      var p = new URLSearchParams();
      p.set('pa', r.m2);
      p.set('pkt', r.paketti.id);
      var extras = ['siivous', 'piano', 'kaluste', 'varastointi'].filter(function (k) { return s[k]; });
      if (extras.length) { p.set('lisa', extras.join(',')); }
      if (r.paketti.laatikot || s.vuokra) { p.set('laatikot', s.laatikoita); }
      if (s.vuokra) { p.set('paivat', s.paivia); }
      p.set('arvio', Math.floor(r.yhteensa[0] / 100) + '-' + Math.ceil(r.yhteensa[1] / 100));
      if (s.siirto && (s.siirto.tila === 'ok' || s.siirto.tila === 'sama')) {      // the cities go to the form's town fields
        p.set('lahto', s.siirto.lahto);
        p.set('kohde', s.siirto.kohde);
        if (s.siirto.km) { p.set('km', s.siirto.km); }
      }
      return DATA.linkit.tarjous + (DATA.linkit.tarjous.indexOf('?') === -1 ? '?' : '&') + p.toString();
    }

    // Measurement: the choices and the price range the visitor sees go to the dataLayer, and Google Tag Manager
    // forwards them to Google Analytics 4 (one trigger and one tag per event, named "Custom Event - calculator_…" and
    // "GA4 Event - calculator_…"). Nothing the visitor types is sent as such, only the size, package, extras and the
    // price range of the result.
    //   calculator_view       the size slider has been fully on screen for 0.8 s (once per page view)
    //   calculator_start      the first choice made (once); calc_control: size | package | extras | box_rental
    //   calculator_estimate   the choices have settled for 1.5 s and the result differs from the last one sent (max 10)
    //   calculator_over_limit the bigger-than-150 m² notice was shown (once)
    //   calculator_cta_click  a quote or order link was clicked; calc_cta: quote | quote_bar | order_now | quote_over_limit
    // calculator_estimate and calculator_cta_click carry calc_m2, calc_size_class, calc_package, calc_extras,
    // calc_price_low and calc_price_high.
    var track = (function () {
      var sentOnce = {}, lastEstimate = '', estimates = 0, timer = null;

      function push(name, data) {
        var message = { event: name }, key;
        for (key in data) { if (Object.prototype.hasOwnProperty.call(data, key)) { message[key] = data[key]; } }
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push(message);
      }
      function once(name, data) {
        if (!sentOnce[name]) { sentOnce[name] = true; push(name, data); }
      }
      function snapshot() {
        var s = readState(), c = current.luokka;
        var extras = ['siivous', 'piano', 'kaluste', 'varastointi'].filter(function (k) { return s[k]; });
        if (s.vuokra) { extras.push('vuokra'); }
        if (current.siirto) { extras.push('toinen_kaupunki'); }       // the distance fee applies (more than 50 km)
        return {
          calc_m2: current.m2,
          calc_size_class: c.min + '-' + (c.max === null ? 'plus' : c.max),
          calc_package: current.paketti.id,
          calc_extras: extras.length ? extras.join(',') : 'none',
          calc_price_low: Math.floor(current.yhteensa[0] / 100),
          calc_price_high: Math.ceil(current.yhteensa[1] / 100)
        };
      }
      function estimate() {
        window.clearTimeout(timer);
        timer = null;
        var data = snapshot(), key = JSON.stringify(data);
        if (key === lastEstimate || estimates >= 10) { return; }
        lastEstimate = key;
        estimates += 1;
        push('calculator_estimate', data);
      }

      return {
        /** a choice was made; withoutEstimate: the result did not change (a size above the limit was typed) */
        touch: function (control, withoutEstimate) {
          once('calculator_start', { calc_control: control });
          if (!el.raja.hidden) { once('calculator_over_limit'); }
          if (withoutEstimate) { return; }
          window.clearTimeout(timer);
          timer = window.setTimeout(estimate, 1500);
        },
        cta: function (name) {
          if (timer !== null) { estimate(); }                // the last choices count before the click
          // Always with the result data: Tag Manager keeps the last value of every data layer key, so a message
          // that left some keys out would carry the values of an earlier message into Analytics.
          push('calculator_cta_click', Object.assign({ calc_cta: name }, snapshot()));
        },
        watchView: function () {
          if (!('IntersectionObserver' in window)) { return; }
          var dwell = null;
          var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
              window.clearTimeout(dwell);
              if (entry.isIntersecting) {
                dwell = window.setTimeout(function () { once('calculator_view'); observer.disconnect(); }, 800);
              }
            });
          }, { threshold: 1 });
          observer.observe(el.liuku);
        }
      };
    })();

    function ctaName(target) {
      var a = target && target.closest ? target.closest('a') : null;
      if (!a) { return null; }
      if (a.hasAttribute('data-mhi-quote')) { return 'quote'; }
      if (a.hasAttribute('data-mhi-bar-quote')) { return 'quote_bar'; }
      if (a.hasAttribute('data-mhi-order')) { return 'order_now'; }
      return el.raja.contains(a) ? 'quote_over_limit' : null;
    }
    function onClick(e) {
      var name = ctaName(e.target);
      if (name) { track.cta(name); }
    }
    section.addEventListener('click', onClick);
    section.addEventListener('auxclick', function (e) { if (e.button === 1) { onClick(e); } });

    el.koko.addEventListener('input', function () {
      var v = readInt(el.koko, K.min, K.max);
      if (v === null) {                                      // a half typed value: keep the previous result
        var typed = parseFloat(String(el.koko.value).replace(',', '.'));
        var tooBig = isFinite(typed) && typed > K.max;       // a bigger home gets the quote advice instead of the range hint
        el.vihje.hidden = tooBig;
        el.raja.hidden = !tooBig;
        if (tooBig) { track.touch('size', true); }
        return;
      }
      el.vihje.hidden = true;
      setSize(v);
      update();
      track.touch('size');
    });
    el.koko.addEventListener('change', function () {         // leaving the field: clamp to the allowed range
      var raw = Math.round(parseFloat(String(el.koko.value).replace(',', '.')));
      setSize(isFinite(raw) ? clamp(raw, K.min, K.max) : m2);
      el.vihje.hidden = true;
      update();
      track.touch('size');
    });
    el.liuku.addEventListener('input', function () {
      el.vihje.hidden = true;
      setSize(parseInt(el.liuku.value, 10));
      update();
      track.touch('size');
    });

    if (hasCity) {
      el.kaupunki.addEventListener('change', function () {
        el.kaupunkiKentat.hidden = !el.kaupunki.checked;
        if (el.kaupunki.checked) { fillCities(); }
        update();
        track.touch('city');
      });
      [el.lahto, el.kohde].forEach(function (field) {
        field.addEventListener('input', function () { update(); track.touch('city'); });
      });
      el.kaupunkiKentat.hidden = !el.kaupunki.checked;       // a browser may restore the checkbox as ticked
      if (el.kaupunki.checked) { fillCities(); }
    }

    head.querySelectorAll('input[name="mhi-paketti"], input[name="mhi-lisa"], #mhi-vuokra').forEach(function (e) {
      e.addEventListener('change', function () {
        update();
        track.touch(e.id === 'mhi-vuokra' ? 'box_rental' : (e.name === 'mhi-paketti' ? 'package' : 'extras'));
      });
    });

    // WebMCP: an agent working in a browser can ask for the calculator's estimate and open the quote form with the
    // choices filled in. The form is never sent from here; a person checks it, adds the contact details and sends it.
    function registerWebMcp() {
      var mc = document.modelContext || navigator.modelContext;      // older Chrome builds: navigator.modelContext
      if (!mc || typeof mc.registerTool !== 'function') { return; }

      var packageIds = DATA.paketit.map(function (p) { return p.id; });
      var inputSchema = {
        type: 'object',
        properties: {
          size_m2: { type: 'integer', minimum: K.min, maximum: K.max, description: 'Apartment size in square metres, ' + K.min + '–' + K.max + '. Bigger homes need a quote.' },
          package: { type: 'string', enum: packageIds, description: 'perus = move without boxes (default), laatikot = move and moving boxes, taysi = full service (packing, transport, unpacking), taysi-laatikot = full service and boxes.' },
          extras: { type: 'array', items: { type: 'string', enum: ['siivous', 'piano', 'kaluste', 'varastointi'] }, description: 'siivous = move-out cleaning, piano = piano or safe, kaluste = furniture assembly, varastointi = storage.' },
          box_rental: { type: 'boolean', description: 'Rent moving boxes separately. Only for packages without boxes.' },
          from_city: { type: 'string', description: 'Only when moving to another city: the Finnish municipality moved from, e.g. Helsinki. Give both from_city and to_city; moves of more than 50 km get a distance fee of ' + String(DATA.kaupunkimatka.euroaKm).replace('.', ',') + ' euros per kilometre.' },
          to_city: { type: 'string', description: 'Only when moving to another city: the Finnish municipality moved to, e.g. Tampere.' }
        },
        required: ['size_m2']
      };

      function absolute(url) { return new URL(url, window.location.href).href; }
      function plain(html) { return (new DOMParser().parseFromString(String(html), 'text/html').body.textContent || '').replace(/\s+/g, ' ').trim(); }
      function reply(data) { return { content: [{ type: 'text', text: JSON.stringify(data) }] }; }
      function euros(pair) { return { low: Math.floor(pair[0] / 100), high: Math.ceil(pair[1] / 100) }; }
      function stateFrom(args) {
        args = args || {};
        var size = Math.round(Number(args.size_m2));
        if (!isFinite(size) || size < K.min || size > K.max) { return null; }
        var id = packageIds.indexOf(args.package) !== -1 ? args.package : packageIds[0];
        var pkg = DATA.paketit.filter(function (p) { return p.id === id; })[0];
        var extras = Array.isArray(args.extras) ? args.extras : [];
        return {
          m2: size, paketti: id,
          siivous: extras.indexOf('siivous') !== -1, piano: extras.indexOf('piano') !== -1,
          kaluste: extras.indexOf('kaluste') !== -1, varastointi: extras.indexOf('varastointi') !== -1,
          vuokra: args.box_rental === true && !pkg.laatikot,
          laatikoita: Laskenta.suositusLaatikot(size),
          paivia: DATA.laatikkovuokra.oletusPaivat,
          siirto: args.from_city || args.to_city ? Laskenta.siirto(args.from_city || '', args.to_city || '') : null
        };
      }
      var badSize = { error: 'size_m2 must be a whole number from ' + K.min + ' to ' + K.max + '. For bigger homes, request a quote.', quote_request_url: absolute(DATA.linkit.tarjous) };

      var tools = [{
        name: 'estimate_moving_price',
        description: 'Estimate the price of a home move in Finland with Muuttohaukat\'s own calculator (its published price list). Returns a price range in euros for the apartment size, package and extras, with notes. An estimate, not a binding offer.',
        inputSchema: inputSchema,
        annotations: { readOnlyHint: true },
        execute: function (args) {
          var s = stateFrom(args);
          if (!s) { return reply(badSize); }
          var r = Laskenta.laske(s);
          return reply({
            size_m2: r.m2,
            size_class: plain(Laskenta.luokanNimi(r.luokka)),
            package: r.paketti.id,
            estimate_eur: euros(r.yhteensa),
            inter_city: s.siirto ? { status: ({ ok: 'ok', sama: 'same', puuttuu: 'missing', tuntematon: 'unknown' })[s.siirto.tila], from: s.siirto.lahto || null, to: s.siirto.kohde || null, km: s.siirto.km || null, fee_eur: r.siirto ? r.siirto.euroa : 0 } : null,
            included: r.mukana.map(function (x) { return { item: plain(x.nimi), eur: euros(x.hinta) }; }),
            not_included: r.eiMukana.map(function (x) { return { item: plain(x.nimi), note: plain(x.teksti) }; }),
            notes: r.huomiot.map(plain),
            tilaamuutto_order_possible: r.tilaaHeti,
            tilaamuutto_url: r.tilaaHeti ? DATA.linkit.tilaaHeti : null,
            quote_request_url: absolute(quoteLinkFor(r, s)),
            disclaimer: 'Estimate from Muuttohaukat\'s published price list. The final price is confirmed in the offer.'
          });
        }
      }, {
        name: 'open_prefilled_quote_request',
        description: 'Open Muuttohaukat\'s home move quote form with the size, package and extras already filled in. It does NOT send the form: the person must enter the move date, the addresses and the contact details, check everything and send it themselves.',
        inputSchema: inputSchema,
        execute: function (args) {
          var s = stateFrom(args);
          var url = absolute(s ? quoteLinkFor(Laskenta.laske(s), s) : DATA.linkit.tarjous);
          window.setTimeout(function () { window.location.assign(url); }, 150);
          return reply({ opened: url, sent: false, note: 'The form opens for the person to complete and send. Do not send it without their confirmation of the details.' });
        }
      }];

      tools.forEach(function (tool) {
        try {
          var registered = mc.registerTool(tool);
          if (registered && typeof registered.catch === 'function') { registered.catch(function () {}); }
        } catch (e) { /* the API is optional */ }
      });
    }

    el.tilaa.setAttribute('href', DATA.linkit.tilaaHeti);
    setSize(K.oletus);
    update();
    watchBar();
    track.watchView();
    registerWebMcp();
  }

  function start() {
    var calculator = document.querySelector('[data-mhi="laskuri"]');
    if (calculator) { initCalculator(calculator); }
  }

  if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', start); } else { start(); }
})();
