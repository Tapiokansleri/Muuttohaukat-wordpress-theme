<?php
namespace Muuttohaukat\Templates;

function FormTimeframe() {
  ?>
<div class="w-full md:pr-2">
  <h4>Ajankohta</h4>
</div>
<div class="w-full md:pr-2">
  <label>
    Muuttopäivä tai laatikkotoimituspäivä

    <input type="date" name="Muuttopvm" value="<?=date('Y-m-d', strtotime('+ 14 day'))?>" min="<?=date("Y-m-d")?>" />
  </label>
</div>
  <?php
}
