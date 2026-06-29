<?php

namespace Muuttohaukat\Templates;

function FormAccessoriesOnly($data = []) {
?>
  <p>Tähdellä (*) merkityt kentät ovat pakollisia.</p>

  <?php
  FormAccessories(false, false);
  FormSourceAndTarget();
  FormContact(true);
  // FormTimeframe();
  // FormAdditionalServices();
  FormSubmit();
}
