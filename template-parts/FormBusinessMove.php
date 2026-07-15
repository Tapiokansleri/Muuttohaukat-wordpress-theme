<?php

namespace Muuttohaukat\Templates;

function FormBusinessMove($data = []) {
?>
  <p>Tähdellä (*) merkityt kentät ovat pakollisia.</p>

  <?php
  FormContact(true);
  FormSourceAndTarget("Toimitilat", ["Toimisto", "Varasto", "Liikehuoneisto", "Muu"]);
  FormTimeframe();
  FormAccessories(true);
  FormAdditionalServices(true);
  FormSubmit();
}
