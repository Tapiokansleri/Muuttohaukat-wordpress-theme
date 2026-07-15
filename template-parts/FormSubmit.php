<?php

namespace Muuttohaukat\Templates;

function FormSubmit($data = []) {
?>

  <div class="w-full md:pr-2">
    <h4>Lisätiedot</h4>
  </div>
  <div class="w-full md:pr-2">
    <textarea name="Lisätiedot" cols="40" rows="10" placeholder="Esimerkiksi vaihtoehtoiset muuttopäivät"></textarea>
  </div>

  <p>Tähdellä (*) merkityt kentät ovat pakollisia.</p>


  <input type="hidden" name="Referrer" value="<?=\Muuttohaukat\currentUrl()?>" />

  <label class="w-full inline-flex items-center">
    <input type="checkbox" name="Käyttöehdot" value="Hyväksytty" required />
    <p>
      Hyväksyn tietojeni käytön tietosuojaselosteen mukaisesti. *

      <br><br>
      <a href="/tietosuoja" target="_blank" rel="noopener">Tietosuojaseloste</a>
    </p>
  </label>

  <div class="mt-4 mb-8">
    <input type="submit" value="Lähetä" />
  </div>

<?php
}
