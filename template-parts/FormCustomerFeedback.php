<?php

namespace Muuttohaukat\Templates;

function FormCustomerFeedback($data = []) {
?>
<p>Tähdellä (*) merkityt kentät ovat pakollisia.</p>
<div class="flex flex-wrap ">
  <div class="md:w-1/2 md:pr-2 w-full">
    <label>Tilausnumero <input type="text" name="Tilausnumero" /> </label>
  </div>

  <div class="md:w-1/2 md:pr-2 w-full">
    <label>Mistä kuulit meistä? <select name="TietoYrityksestä">
      <option>Valitse...</option>
      <option value="Olen käyttänyt Muuttohaukkoja aikaisemmin">Olen käyttänyt Muuttohaukkoja aikaisemmin</option>
      <option value="Sosiaalisen median (Facebook, Twitter, yms. kautta)">
        Sosiaalisen median (Facebook, Twitter, yms. kautta)
      </option>
      <option value="Hakukoneen (Google, Bing yms. kautta)">Hakukoneen (Google, Bing yms. kautta)</option>
      <option value="Ystävältä tai tutulta">Ystävältä tai tutulta</option>
      <option value="Muun median kautta (tv, lehdet jne.)">Muun median kautta (tv, lehdet jne.)</option>
    </select></label>
  </div>
</div>

<div class="w-full md:pr-2">
  <h4>Asiakaspalvelun laatu</h4>
  <label>1. Sain yhteyden asiakaspalveluhenkilökuntaan vaivattomasti *
    <select name="YhteysAsiakaspalveluun" required>
      <option value="">Valitse...</option>
      <option value="5">Täysin samaa mieltä</option>
      <option value="4">Jokseenkin samaa mieltä</option>
      <option value="3">Ei samaa eikä eri mieltä</option>
      <option value="2">Jokseenkin eri mieltä</option>
      <option value="1">Täysin eri mieltä</option>
    </select>
  </label>
  <label
    >2. Minua palveltiin ystävällisesti ja asiantuntevasti *
    <select name="YstävällinenJaAsiantuntevaPalvelu" required>
      <option value="">Valitse...</option>
      <option value="5">Täysin samaa mieltä</option>
      <option value="4">Jokseenkin samaa mieltä</option>
      <option value="3">Ei samaa eikä eri mieltä</option>
      <option value="2">Jokseenkin eri mieltä</option>
      <option value="1">Täysin eri mieltä</option>
    </select>
  </label>
  <br />
  <label
    >3. Tilauksen tekeminen ja aikataulusta sopiminen sujuivat helposti *
    <select name="TilauksenTekeminenJaAikataulutusHelppoa" required>
      <option value="">Valitse...</option>
      <option value="5">Täysin samaa mieltä</option>
      <option value="4">Jokseenkin samaa mieltä</option>
      <option value="3">Ei samaa eikä eri mieltä</option>
      <option value="2">Jokseenkin eri mieltä</option>
      <option value="1">Täysin eri mieltä</option>
    </select>
  </label>
  <h4>Muuttotyö / Muuttotiimin ammattitaito</h4>
  <label
    >4. Työntekijät olivat ammattitaitoisia ja asiakaspalveluhenkisiä *
    <select name="TyöntekijätAmmattitaitoisiaJaAsiakaspalveluhenkisiä" required>
      <option value="">Valitse...</option>
      <option value="5">Täysin samaa mieltä</option>
      <option value="4">Jokseenkin samaa mieltä</option>
      <option value="3">Ei samaa eikä eri mieltä</option>
      <option value="2">Jokseenkin eri mieltä</option>
      <option value="1">Täysin eri mieltä</option>
    </select>
  </label>
  <label
    >5. Työntekijät olivat ajoissa paikalla ja kertoivat muuttosuunnitelman *
    <select name="AjoissaPaikalla" required>
      <option value="">Valitse...</option>
      <option value="5">Täysin samaa mieltä</option>
      <option value="4">Jokseenkin samaa mieltä</option>
      <option value="3">Ei samaa eikä eri mieltä</option>
      <option value="2">Jokseenkin eri mieltä</option>
      <option value="1">Täysin eri mieltä</option>
    </select>
  </label>
  <label
    >6. Tavaroita käsiteltiin asianmukaisesti ja toiminta oli ripeää *
    <select name="AsianmukainenRipeäKäsittely" required>
      <option value="">Valitse...</option>
      <option value="5">Täysin samaa mieltä</option>
      <option value="4">Jokseenkin samaa mieltä</option>
      <option value="3">Ei samaa eikä eri mieltä</option>
      <option value="2">Jokseenkin eri mieltä</option>
      <option value="1">Täysin eri mieltä</option>
    </select>
  </label>
  <label
    >7. Työntekijät olivat siististi pukeutuneet ja käyttivät apuvälineitä työnteossa hyödykseen *
    <select name="SiististiPukeutuneetJaVälineetHallussa" required>
      <option value="">Valitse...</option>
      <option value="5">Täysin samaa mieltä</option>
      <option value="4">Jokseenkin samaa mieltä</option>
      <option value="3">Ei samaa eikä eri mieltä</option>
      <option value="2">Jokseenkin eri mieltä</option>
      <option value="1">Täysin eri mieltä</option>
    </select>
  </label>
  <h4>Yleistä</h4>
  <label
    >8. Palvelun laatu täytti odotukseni *
    <select name="TäyttiOdotukset" required>
      <option value="">Valitse...</option>
      <option value="5">Täysin samaa mieltä</option>
      <option value="4">Jokseenkin samaa mieltä</option>
      <option value="3">Ei samaa eikä eri mieltä</option>
      <option value="2">Jokseenkin eri mieltä</option>
      <option value="1">Täysin eri mieltä</option>
    </select>
  </label>
  <label
    >9. Maksamani hinta suhteessa palvelun laatuun oli kohdallaan *
    <select name="HintaKohdallaan" required>
      <option value="">Valitse...</option>
      <option value="5">Täysin samaa mieltä</option>
      <option value="4">Jokseenkin samaa mieltä</option>
      <option value="3">Ei samaa eikä eri mieltä</option>
      <option value="2">Jokseenkin eri mieltä</option>
      <option value="1">Täysin eri mieltä</option>
    </select>
  </label>
  <label
    >10. Suosittelisitteko yrityksemme palveluja läheisillenne tai tuttavillenne? *
    <select name="Suosittelisitko" required>
      <option value="">Valitse...</option>
      <option value="Kyllä">Kyllä</option>
      <option value="En">En</option>
      <option value="Varauksin">Varauksin</option>
    </select>
  </label>
</div>

<div class="w-full md:pr-2">
  <h4>Vapaa palaute</h4>
  <label>Risut ja ruusut
    <textarea name="VapaaPalaute"></textarea>
  </label>
</div>

<div class="w-full md:pr-2">
  <h4>Yhteystiedot</h4>
  <p>
    Yhteystietonsa palautteen yhteydessä ilmoittaneiden kesken arvotaan kuukausittain Muuttohaukat-aiheisia tuotteita!
  </p>
</div>
<div class="xl:w-1/2">
  <label>
    Nimi <br />
    <div class="form-control-wrap your-name">
      <input type="text" name="Nimi" />
    </div>
  </label>
</div>
<div class="xl:w-1/2">
  <label>
    Puhelin <br />
    <div class="form-control-wrap tel-puhnro">
      <input type="tel" name="Numero" />
    </div>
  </label>
</div>
<div class="xl:w-1/2">
  <label>
    Sähköposti <br />
    <div class="form-control-wrap your-email">
      <input type="email" name="Email" />
    </div>
  </label>
</div>
<div class="xl:w-1/2">
  <label>
    Osoite <br />
    <div class="form-control-wrap text-osoite">
      <input type="text" name="Osoite" />
    </div>
  </label>
</div>
<div class="w-full md:pr-2">
  <label>
    Postinumero ja -toimipaikka <br />
    <div class="form-control-wrap text-postinro">
      <input type="text" name="Postinro" />
    </div>
  </label>
</div>

<?php

  FormSubmit();
}
