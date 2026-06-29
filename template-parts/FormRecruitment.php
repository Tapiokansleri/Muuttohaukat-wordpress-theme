<?php

namespace Muuttohaukat\Templates;

function FormRecruitment($data = []) {
?>
  <p>Tähdellä (*) merkityt kentät ovat pakollisia.</p>

  <?php
  FormContact(false);
  // FormTimeframe();
  ?>
  <h4>Perustiedot</h4>

  <div class="flex flex-wrap">
    <div class="w-full md:w-1/2 md:pr-2">
      <label>Aloitusajankohta *

        <input
          type="date"
          name="Aloitusajankohta"
          required
          value="<?=date('Y-m-d', strtotime('+ 14 day'))?>"
        />
      </label>
    </div>

    <div class="w-full md:w-1/2 md:pr-2">
      <label>Palkkatoivomus
        <input
        type="number"
        name="Palkkatoivomus"
      />
      </label>
    </div>

    <div class="w-full md:w-1/2 md:pr-2">
      <label>Ajokortti
      <input
        type="text"
        name="Ajokortti"
        placeholder="C"
      />
      </label>
    </div>

    <div class="w-full md:w-1/2 md:pr-2">
      <label>Ammattipätevyys voimassa? *
        <select name="AmmattipätevyysVoimassa" required>
          <option value="">Valitse</option>
          <option value="Ei">Ei</option>
          <option value="C">Kyllä</option>
        </select>
      </label>
    </div>

    <div class="w-full md:w-1/2 md:pr-2">
      <label>Toimipiste johon haet *
        <input
        type="text"
        name="Toimipiste"
        placeholder="Hyvinkää"
        required
      />
      </label>
    </div>

    <div class="w-full md:pr-2">
      <label>Työkokemus & koulutus
        <textarea name="KokemusJaKoulutus"></textarea>
      </label>
    </div>

    <div class="w-full md:pr-2">
      <label>Mitä odotat työltä?
        <textarea name="Odotukset"></textarea>
      </label>
    </div>
  </div>

  <?php
  // FormAdditionalServices();
  FormSubmit();
}
