<?php
/**
 * Title: Kaupunkisivu
 * Slug: muuttohaukat/kaupunkisivu
 * Categories: mh-landing
 * Description: Paikkakuntasivun runko: sisäänheitto, paikallinen palvelu toimipisteineen, hinnat, lähikunnat ja toimintakehotus.
 * Keywords: kaupunki, paikkakunta, muuttopalvelu, toimipiste
 *
 * Vaihda Kaupunki-sanat paikkakunnan nimeen, valitse toimipiste
 * Paikallinen palvelu -lohkon asetuksista ja kirjoita lähikunnat
 * Lähikunnat-lohkon Linkit-paneeliin.
 */
$theme_uri = esc_url(get_template_directory_uri());
?>
<!-- wp:muuttohaukat/landing-hero -->
<!-- wp:group {"className":"mh-landing__inner mh-landing-hero__inner"} -->
<div class="wp-block-group mh-landing__inner mh-landing-hero__inner"><!-- wp:group {"className":"mh-landing-hero__content"} -->
<div class="wp-block-group mh-landing-hero__content"><!-- wp:paragraph {"className":"mh-landing-eyebrow"} -->
<p class="mh-landing-eyebrow">Muuttohaukat · vuodesta 1992</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"mh-landing-hero__title"} -->
<h1 class="wp-block-heading mh-landing-hero__title">Muuttopalvelu Kaupunki</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"mh-landing-hero__lead"} -->
<p class="mh-landing-hero__lead">Hoidamme koti- ja yritysmuutot Kaupungissa. Saat kantajat, muuttoauton ja muuttolaatikot samalta toimittajalta, ja muutto on vakuutettu. Alle 60 neliön asunnon muutosta saat tarjouksen heti.</p>
<!-- /wp:paragraph -->

<!-- wp:muuttohaukat/buttons {"button1Text":"Pyydä tarjous","button1Url":"/tarjouspyynto/kotimuutto/","button1Color":"yellow","button2Text":"Tilaa heti","button2Url":"https://tilaamuutto.fi","button2Target":"_blank","button2Color":"black"} -->
<div class="wp-block-muuttohaukat-buttons mh-painike-wrap"><a class="mh-painike mh-painike--yellow" href="/tarjouspyynto/kotimuutto/" target="_self">Pyydä tarjous</a><a class="mh-painike mh-painike--black" href="https://tilaamuutto.fi" target="_blank" rel="noopener noreferrer">Tilaa heti</a></div>
<!-- /wp:muuttohaukat/buttons -->

<!-- wp:group {"className":"mh-landing-hero__trust"} -->
<div class="wp-block-group mh-landing-hero__trust"><!-- wp:muuttohaukat/icon-item {"variant":"trust","icon":"fa-solid fa-medal","title":"30+ v","description":"kokemusta muuttopalveluista"} -->
<div class="wp-block-muuttohaukat-icon-item mh-icon-item mh-icon-item--trust"><p class="mh-icon-item__label"><i class="mh-landing-icon fa-solid fa-medal" aria-hidden="true"></i><span>30+ v</span></p><p class="mh-icon-item__caption">kokemusta muuttopalveluista</p></div>
<!-- /wp:muuttohaukat/icon-item -->

<!-- wp:muuttohaukat/icon-item {"variant":"trust","icon":"fa-solid fa-location-dot","title":"Paikallinen","description":"muutto oman alueen toimipisteestä"} -->
<div class="wp-block-muuttohaukat-icon-item mh-icon-item mh-icon-item--trust"><p class="mh-icon-item__label"><i class="mh-landing-icon fa-solid fa-location-dot" aria-hidden="true"></i><span>Paikallinen</span></p><p class="mh-icon-item__caption">muutto oman alueen toimipisteestä</p></div>
<!-- /wp:muuttohaukat/icon-item -->

<!-- wp:muuttohaukat/icon-item {"variant":"trust","icon":"fa-solid fa-box","title":"Laatikot","description":"toimitettuna ja noudettuna"} -->
<div class="wp-block-muuttohaukat-icon-item mh-icon-item mh-icon-item--trust"><p class="mh-icon-item__label"><i class="mh-landing-icon fa-solid fa-box" aria-hidden="true"></i><span>Laatikot</span></p><p class="mh-icon-item__caption">toimitettuna ja noudettuna</p></div>
<!-- /wp:muuttohaukat/icon-item --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:image {"className":"mh-landing-hero__media","url":"<?php echo $theme_uri; ?>/assets/img/haukka.png","alt":"Muuttohaukkojen muuttoauto ja kantajat"} -->
<figure class="wp-block-image mh-landing-hero__media"><img src="<?php echo $theme_uri; ?>/assets/img/haukka.png" alt="Muuttohaukkojen muuttoauto ja kantajat"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->
<!-- /wp:muuttohaukat/landing-hero -->

<!-- wp:muuttohaukat/landing-local {"office":"hyvinkaa","city":"Kaupunki"} -->
<!-- wp:group {"className":"mh-landing-sec-head"} -->
<div class="wp-block-group mh-landing-sec-head"><!-- wp:paragraph {"className":"mh-landing-kicker"} -->
<p class="mh-landing-kicker">Paikallinen palvelu</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Muutto Kaupungissa hoituu lähimmästä toimipisteestä</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:paragraph -->
<p>Kerro tässä, mistä toimipisteestä muutto lähtee, kuinka pitkä matka on ja mitkä kunnat hoituvat samalla käynnillä.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Saat muuttoauton, kantajat ja muuttolaatikot samalta toimittajalta. Lisäksi hoidamme pakkauksen, muuttosiivouksen, varastoinnin sekä pianon ja kassakaapin siirron.</p>
<!-- /wp:paragraph -->
<!-- /wp:muuttohaukat/landing-local -->

<!-- wp:muuttohaukat/landing-prices {"background":"yellow"} -->
<!-- wp:group {"className":"mh-landing-sec-head"} -->
<div class="wp-block-group mh-landing-sec-head"><!-- wp:paragraph {"className":"mh-landing-kicker"} -->
<p class="mh-landing-kicker">Hinnat</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Mitä muutto Kaupungissa maksaa</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->
<!-- /wp:muuttohaukat/landing-prices -->

<!-- wp:muuttohaukat/landing-nearby {"links":[],"hub":"/hyvinkaa-uusimaa/","hubLabel":"Hyvinkää ja Uusimaa"} -->
<!-- wp:group {"className":"mh-landing-sec-head"} -->
<div class="wp-block-group mh-landing-sec-head"><!-- wp:paragraph {"className":"mh-landing-kicker"} -->
<p class="mh-landing-kicker">Lähikunnat</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Muutot myös naapurikuntiin</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->
<!-- /wp:muuttohaukat/landing-nearby -->

<!-- wp:muuttohaukat/landing-final-cta {"background":"black"} -->
<!-- wp:group {"className":"mh-landing__inner mh-landing-final"} -->
<div class="wp-block-group mh-landing__inner mh-landing-final"><!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Pyydä tarjous muutosta Kaupungissa</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Kerro asunnon koko ja muuttopäivä, niin saat hinnan. Alle 60 neliön asunnosta tarjous tulee heti, suuremmista viimeistään seuraavana arkipäivänä.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"mh-landing-final__actions"} -->
<div class="wp-block-buttons mh-landing-final__actions"><!-- wp:button {"className":"mh-landing__button mh-landing__button--primary"} -->
<div class="wp-block-button mh-landing__button mh-landing__button--primary"><a class="wp-block-button__link wp-element-button" href="/tarjouspyynto/kotimuutto/">Pyydä tarjous</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"mh-landing__button mh-landing__button--ghost"} -->
<div class="wp-block-button mh-landing__button mh-landing__button--ghost"><a class="wp-block-button__link wp-element-button" href="https://tilaamuutto.fi">Tilaa heti: tilaamuutto.fi</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->
<!-- /wp:muuttohaukat/landing-final-cta -->
