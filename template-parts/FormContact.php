<?php
namespace Muuttohaukat\Templates;

function FormContact($business = false) {
  $title = $business ? 'Yhteyshenkilön tiedot' : 'Yhteystietosi';


  ?>
  <div class="flex flex-col">
    <div class="w-full md:pr-2">
      <h4><?=$title?></h4>
    </div>

    <div class="flex flex-wrap">
      <div class="w-full md:w-1/2 md:pr-2">
        <label>
          Nimi *

          <input type="text" name="Nimi" required maxlength="100"/>
        </label>
      </div>
      <div class="w-full md:w-1/2 md:pr-2">
        <label>
          Sähköposti *

          <input type="email" name="Email" required />
        </label>
      </div>
      <div class="w-full md:pr-2">
        <label>
          Puhelinnumero *

          <input type="tel" class="c-input" name="Puhnro" required />
        </label>
      </div>
    </div>

    <?php
    if ($business) { ?>
    <div class="flex flex-wrap">
      <div class="w-full md:w-1/2 md:pr-2">
        <label>
          Yrityksen nimi

          <input type="text" name="YrityksenNimi" />
        </label>
      </div>
      <div class="w-full md:w-1/2 md:pr-2">
        <label>
          Y-tunnus

          <input type="text" name="Y-tunnus" maxlength="10" />
        </label>
      </div>
    </div>
    <?php } ?>
  </div>
  <?php
}
