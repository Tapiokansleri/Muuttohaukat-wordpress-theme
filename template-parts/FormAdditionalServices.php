<?php
namespace Muuttohaukat\Templates;

function FormAdditionalServices($includeInteriorDesign = false) {
  ?>
<div class="flex flex-wrap">

<div class="w-full md:pr-2">
  <h4>Muuttoon haluttavat lisäpalvelut</h4>
</div>

<div class="w-10/12 md:w-1/2 md:pr-2 mx-auto <?=!$includeInteriorDesign ? "hidden" : ""?>">
  <div class="form-control">
    <label class="label cursor-pointer">
      <span class="label-text">Sisustussuunnittelu</span>
      <input name="Sisustussuunnittelu" value="Kyllä" type="checkbox" class="toggle toggle-accent"  />
    </label>
  </div>
</div>

<div class="w-10/12 md:w-1/2 md:pr-2 mx-auto">
  <div class="form-control">
    <label class="label cursor-pointer">
      <span class="label-text">Pakkauspalvelu</span>
      <input name="Pakkauspalvelu" value="Kyllä" type="checkbox" class="toggle toggle-accent"  />
    </label>
  </div>
</div>

<div class="w-10/12 md:w-1/2 md:pr-2 mx-auto">
  <div class="form-control">
    <label class="label cursor-pointer">
      <span class="label-text">Kalusteasennus</span>
      <input name="Kalusteasennus" value="Kyllä" type="checkbox" class="toggle toggle-accent"  />
    </label>
  </div>
</div>

<div class="w-10/12 md:w-1/2 md:pr-2 mx-auto">
  <div class="form-control">
    <label class="label cursor-pointer">
      <span class="label-text">Varastointi</span>
      <input name="Varastointi" value="Kyllä" type="checkbox" class="toggle toggle-accent"  />
    </label>
  </div>
</div>

<div class="w-10/12 md:w-1/2 md:pr-2 mx-auto">
  <div class="form-control">
    <label class="label cursor-pointer">
      <span class="label-text">Muuttosiivous lähtöpaikassa</span>
      <input name="MuuttosiivousLähtöpaikassa" value="Kyllä" type="checkbox" class="toggle toggle-accent"  />
    </label>
  </div>
</div>

<div class="w-10/12 md:w-1/2 md:pr-2 mx-auto">
  <div class="form-control">
    <label class="label cursor-pointer">
      <span class="label-text">Muuttosiivous kohteessa</span>
      <input name="MuuttosiivousKohteessa" value="Kyllä" type="checkbox" class="toggle toggle-accent"  />
    </label>
  </div>
</div>

<div class="w-10/12 md:w-1/2 md:pr-2 mx-auto">
  <div class="form-control">
    <label class="label cursor-pointer">
      <span class="label-text">Pianon / kassakaapin muutto</span>
      <input name="PianonTaiKassakaapinMuutto" value="Kyllä" type="checkbox" class="toggle toggle-accent"  />
    </label>
  </div>
</div>
</div>
  <?php
}
