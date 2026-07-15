<?php
namespace Muuttohaukat\Templates;

function FormSourceAndTarget($type = "Asuntotyyppi", $types = ["Kerrostalo", "Rivitalo", "Omakotitalo", "Vapaa-ajan asunto", "Muu"]) {
  $debug = \Muuttohaukat\isDev() && current_user_can('edit_posts');

  ?>
  <div class="flex flex-wrap">
    <div class="w-full md:pr-2">
      <h4>Toimipiste</h4>

      <p>Valitse mihin toimipisteeseemme lomakkeesi lähetetään käsiteltäväksi.</p>
    </div>

    <div class="w-full md:pr-2">
      <label>
        Toimipiste *

        <select name="Toimipiste" required>
          <option value="">Valitse</option>
          <option value="helsinki@muuttohaukat.com">Helsinki</option>
          <option value="muuttohaukat@muuttohaukat.com">Vantaa</option>
          <option value="turku@muuttohaukat.com">Turku, Varsinais-Suomi & Satakunta</option>
          <option value="tampere@muuttohaukat.com">Tampere & Pirkanmaa</option>
          <option value="hyvinkaa@muuttohaukat.com">Hyvinkää, Hämeenlinna, Riihimäki ja muu Suomi</option>
          <?=$debug ? '<option value="spam@kisu.li">DEBUG: IT-OSASTO</option>' : ''?>
          <?=$debug ? '<option value="jani.vivolin@muuttohaukat.com">DEBUG: Jani V</option>' : ''?>
          <?=$debug ? '<option value="totte.vivolin@muuttohaukat.com">DEBUG: Totte V</option>' : ''?>
        </select>
      </label>
    </div>
  </div>

  <div class="flex flex-wrap">
    <div class="w-full md:pr-2">
      <h4>Lähtöpaikka</h4>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Osoite

        <input type="text" name="Lähtöosoite" maxlength="100" />
      </label>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Postinumero

        <input type="text" name="Lähtöpostinro" />
      </label>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Kaupunki/kunta

        <input type="text" name="Lähtökunta" />
      </label>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label> <?=\esc_html($type); ?></label>

      <select name="LähtöTyyppi">
        <?php foreach ($types as $t) { ?>
          <option value="<?=\esc_attr($t); ?>"><?=\esc_html($t); ?></option>
        <?php } ?>
      </select>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Pinta-ala (m2)

        <input type="number" placeholder="90" step="1" min="0" name="LähtöPA" />
      </label>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Kerros

        <input type="number" name="LähtöKerros" value="1" step="1" min="1" maxlength="3"/>
      </label>
    </div>

    <div class="w-full md:w-1/2 md:pr-2 mx-auto">
      <div class="form-control">
        <label class="label cursor-pointer">
          <span class="label-text text-xl"><strong>Onko lähtöpaikassa hissi?</strong></span>

          <span>Ei </span>
          <input name="LähtöHissi" type="checkbox" class="toggle toggle-accent"/>
          <span> On</span>

        </label>
      </div>
    </div>
</div>
<div class="flex flex-wrap">

    <div class="w-full md:pr-2">
      <h4>Kohdepaikka</h4>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Osoite

        <input type="text" name="KohdeOsoite" maxlength="100"/>
      </label>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Postinumero

        <input type="text" name="KohdePostinro" />
      </label>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Kaupunki/kunta

        <input type="text" name="KohdeKunta" />
      </label>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label> <?=\esc_html($type); ?></label>

      <select name="KohdeTyyppi">
      <?php foreach ($types as $t) { ?>
        <option value="<?=\esc_attr($t); ?>"><?=\esc_html($t); ?></option>
      <?php } ?>
      </select>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Pinta-ala (m2)

        <input type="number" placeholder="90" step="1" min="0" name="KohdePA" />
      </label>
    </div>
    <div class="w-full md:w-1/2 md:pr-2">
      <label>
        Kerros

        <input type="number" step="1" min="1" value="1" name="KohdeKerros" maxlength="3"/>
      </label>
    </div>

    <div class="w-full md:w-1/2 md:pr-2 mx-auto">
      <div class="form-control">
        <label class="label cursor-pointer">
          <span class="label-text text-xl"><strong>Onko kohteessa hissi?</strong></span>

          <span>Ei</span>
          <input name="KohdeHissi" type="checkbox" class="toggle toggle-accent"  />
          <span>On</span>

        </label>
      </div>
    </div>
</div>
  <?php
}
