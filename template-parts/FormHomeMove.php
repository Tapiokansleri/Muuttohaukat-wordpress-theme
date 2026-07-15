<?php

namespace Muuttohaukat\Templates;

function FormHomeMove($data = []) {
?>
  <p>Tähdellä (*) merkityt kentät ovat pakollisia.</p>

  <?php
  FormContact(false);
  FormSourceAndTarget();
  FormTimeframe();
  FormAccessories(true);
  FormAdditionalServices();
  FormSubmit();
}
