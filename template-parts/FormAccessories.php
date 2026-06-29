<?php
namespace Muuttohaukat\Templates;

function FormAccessories($showHelp = true, $hideMost = true) {
  ?>
<div class="flex flex-wrap">
  <div class="w-full">
    <h4>Muuttotarvikkeet</h4>

    <?php if ($showHelp) { ?>
    <p>Jos et ole varma mitä tarvitset, autamme mieluusti valinnassa. Voit huoletta jättää nämä kentät tyhjiksi.</p>
    <?php } ?>

    <p>Tarkemmat kuvaukset saatavilla olevista tarvikkeista löydät <a href="/muuttotarvikkeet" target="_blank">Tarvikkeet-sivulta.</a>
  </div>

  <div class="w-full">
    <p>Tarvikkeet toimitetaan sinulle ennen muuttoa, ja haetaan pois muuton jälkeen. Huomioi että ajot suoritetaan tiistaisin ja torstaisin, valitsethan toimitus- ja palautuspäivän sen mukaisesti. </p>
  </div>

  <div class="w-full md:w-1/2 md:pr-2">
    <label> Toimituspäivä</label>

      <input
        type="date"
        name="TarvikkeidenToimituspvm"
        placeholder="<?=date('Y-m-d', strtotime('next tuesday'))?>"
        min="<?=date("Y-m-d")?>"
      />
  </div>
  <div class="w-full md:w-1/2 md:pr-2">
    <label> Palautuspäivä</label>
      <input
        type="date"
        name="TarvikkeidenPalautuspvm"
        min="<?=date("Y-m-d")?>"
      />
  </div>

  <div class="w-full md:w-1/2 md:pr-2">
    <label> Muuttolaatikkoja (kpl)</label>
      <input type="number" name="Muuttolaatikkoja" value="0" />
  </div>

  <div class="w-full md:w-1/2 md:pr-2 <?=$hideMost ? "hidden" : ""?>">
    <label> Mappilaatikkoja (kpl)</label>

    <input
      type="number"
      name="Mappilaatikkoja"
      value="0"
    />
  </div>


  <div class="w-full md:w-1/2 md:pr-2">
    <label> Henkarivaatelaatikkoja (kpl)</label>


    <input type="number" name="Henkarilaatikkoja" value="0" />

    <!--
    <p>Näppärä vaatetangolla varustettu laatikko, johon voidaan laittaa kauluspaidat, mekot, ja puvut. Yhteen laatikkoon mahtuu 50cm edestä vaatteita. Sopii hyvin myös kausivaatteiden varastointiin.</p>
    -->
  </div>

  <div class="w-full md:w-1/2 md:pr-2 <?=$hideMost ? "hidden" : ""?>">
    <label> Merkintätarrapaketti (100kpl)</label>
      <input
        type="number"
        name="Merkintätarrapaketti"
        value="0"
      />
  </div>

  <div class="w-full md:w-1/2 md:pr-2 <?=$hideMost ? "hidden" : ""?>">
    <label> Kuplamuovirulla (10m x 60cm)</label>

    <input type="number" name="Kuplamuovirulla" value="0" />
  </div>

  <!-- <div class="w-full md:w-1/2 md:pr-2">
    <label> Repäistävä kuplamuovi (? kpl)</label>


      <input
        type="number"
        name="repäistäväkuplamuovi"
        value="0"
      />

  </div> -->

  <div class="w-full md:w-1/2 md:pr-2 <?=$hideMost ? "hidden" : ""?>">
    <label> Muuttosäkkirulla (10kpl / rulla)</label>

    <input type="number" name="Muuttosäkkirulla" value="0" />
  </div>

  <div class="w-full md:w-1/2 md:pr-2 <?=$hideMost ? "hidden" : ""?>">
    <label> Pakkauspaperi (1 kilo)</label>


    <input type="number" name="Pakkauspaperi" value="0" />
  </div>

  <div class="w-full md:w-1/2 md:pr-2 <?=$hideMost ? "hidden" : ""?>">
    <label> Muuttoteippi (1 rulla)</label>

    <input type="number" name="Muuttoteippi" value="0" />
  </div>

  <div class="w-full md:w-1/2 md:pr-2 <?=$hideMost ? "hidden" : ""?>">
    <label> Käsikiristekalvo (300m x 45cm)</label>

    <input type="number" name="Käsikiristekalvo" value="0" />
  </div>

  <div class="w-full md:w-1/2 md:pr-2 mx-auto <?=$hideMost ? "hidden" : ""?>">
    <label> Käsikiristekalvo (300m x 10cm)</label>

    <input
      type="number"
      name="Käsikiristekalvo100mm"
      value="0"
    />
  </div>
</div>
  <?php
}
