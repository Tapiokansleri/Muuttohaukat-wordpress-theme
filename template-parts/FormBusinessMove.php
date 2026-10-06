<?php

namespace Muuttohaukat\Templates;

function FormBusinessMove($data = []) {
?>
  <p>Tähdellä (*) merkityt kentät ovat pakollisia.</p>

  <?php
  FormContact(true);
  FormSourceAndTarget("Toimitilat", ["Toimisto", "Varasto", "Liikehuoneisto", "Muu"]);
  // Sent to Dynamics inside Lisätiedot, see inc/forms.php.
  ?>
  <div class="flex flex-wrap">
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Työpisteiden määrä

        <input type="number" placeholder="25" step="1" min="0" name="Tyopisteet" />
      </label>
    </div>
  </div>
  <?php
  FormTimeframe();
  FormAccessories(true);
  FormAdditionalServices(true);
  FormSubmit();
}
