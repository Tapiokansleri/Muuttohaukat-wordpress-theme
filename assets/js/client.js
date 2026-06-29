/**
 * Muuttohaukat theme — main client JavaScript.
 * Handles: mobile menu, form validation, WPLF callbacks.
 */
(function () {
  'use strict';

  var translations = (window.wptheme && window.wptheme.translations) || {};

  // -------------------------------------------------------------------
  // Mobile menu toggle
  // -------------------------------------------------------------------
  var menuToggle = document.getElementById('mobile-menu-toggle');
  var navigation = document.getElementById('site-navigation');

  function closeMobileMenu() {
    if (navigation) {
      navigation.classList.remove('is-open');
      document.body.classList.remove('mobile-menu-open');
    }
    if (menuToggle) {
      menuToggle.setAttribute('aria-expanded', 'false');
    }
  }

  if (menuToggle && navigation) {
    menuToggle.addEventListener('click', function () {
      var isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
      menuToggle.setAttribute('aria-expanded', String(!isOpen));
      navigation.classList.toggle('is-open');
      document.body.classList.toggle('mobile-menu-open');
    });
  }

  var menuClose = document.querySelector('.mobile-menu-close');
  if (menuClose) {
    menuClose.addEventListener('click', function () {
      closeMobileMenu();
      if (menuToggle) { menuToggle.focus(); }
    });
  }

  // -------------------------------------------------------------------
  // Dropdown submenus
  //   Desktop: opened purely via CSS `.nav-menu li:hover > .sub-menu` in
  //            header.css. No JS hover handler — having one was redundant
  //            and also caused a double-fire bug on touch devices where
  //            the synthetic mouseenter from the first tap opened the menu
  //            and the click immediately toggled it closed (= second tap
  //            required to see it open).
  //   Mobile:  link navigates as normal; a dedicated injected button
  //            (.submenu-toggle) opens/closes the panel via the
  //            `menu-item--open` class. The button is hidden on desktop
  //            via CSS, so the click handler only fires for touch users.
  // -------------------------------------------------------------------
  var menuParents = document.querySelectorAll('.nav-menu .menu-item-has-children');

  function setOpen(parent, open) {
    parent.classList.toggle('menu-item--open', open);
    var btn = parent.querySelector(':scope > .submenu-toggle');
    if (btn) { btn.setAttribute('aria-expanded', String(open)); }
  }

  menuParents.forEach(function (parent) {
    var link = parent.querySelector(':scope > a');
    if (!link) { return; }

    var toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'submenu-toggle';
    toggle.setAttribute('aria-label', translations['toggleSubmenu'] || 'Avaa alavalikko');
    toggle.setAttribute('aria-expanded', 'false');
    link.insertAdjacentElement('afterend', toggle);

    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      setOpen(parent, !parent.classList.contains('menu-item--open'));
    });
  });

  // -------------------------------------------------------------------
  // Keyboard navigation: Escape closes submenu
  // -------------------------------------------------------------------
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      menuParents.forEach(function (p) { setOpen(p, false); });
      if (navigation && navigation.classList.contains('is-open')) {
        closeMobileMenu();
        if (menuToggle) { menuToggle.focus(); }
      }
    }
  });

  // -------------------------------------------------------------------
  // Date picker: open on click (Chrome fix)
  // -------------------------------------------------------------------
  Array.from(document.querySelectorAll("input[type='date']")).forEach(function (picker) {
    picker.addEventListener('click', function () {
      if ('showPicker' in picker) {
        picker.showPicker();
      }
    });
  });

  // -------------------------------------------------------------------
  // Form date validation
  // -------------------------------------------------------------------
  var deliveryInput = document.querySelector('[name="TarvikkeidenToimituspvm"]');
  var returnInput = document.querySelector('[name="TarvikkeidenPalautuspvm"]');
  var movingInput = document.querySelector('[name="Muuttopvm"]');

  if (returnInput && deliveryInput) {
    var today = new Date();

    var datepickerValidator = function (e) {
      var target = e.target;
      var value = target.value;
      var date = new Date(value);
      var parent = target.parentNode;
      var error = null;

      if (date.toLocaleDateString() === today.toLocaleDateString()) {
        error = translations['dateIsToday'] || 'Päivä ei voi olla tänään.';
      } else if (date < today) {
        error = translations['dateInPast'] || 'Päivä ei voi olla menneisyydessä.';
      } else if (target === returnInput) {
        var rD = new Date(returnInput.value);
        var dD = new Date(deliveryInput.value);
        var mD = movingInput ? new Date(movingInput.value) : null;

        if (rD < dD) {
          error = translations['dateBeforeDelivery'] || 'Päivä ei voi olla ennen toimitusta.';
        } else if (mD && rD < mD) {
          error = translations['dateBeforeMove'] || 'Päivä ei voi olla ennen muuttoa.';
        }
      }

      var notice = parent.querySelector('p.inputnotice');
      if (error) {
        if (!notice) {
          notice = document.createElement('p');
        }
        notice.textContent = error;
        target.value = '';

        if (!notice.classList.contains('inputnotice')) {
          notice.classList.add('inputnotice');
          parent.appendChild(notice);
        }
      } else {
        if (notice) {
          parent.removeChild(notice);
        }
      }
    };

    deliveryInput.addEventListener('change', datepickerValidator);
    returnInput.addEventListener('change', datepickerValidator);
    if (movingInput) {
      movingInput.addEventListener('change', datepickerValidator);
    }
  }

  // -------------------------------------------------------------------
  // WPLF (LibreForm) success / error callbacks
  // -------------------------------------------------------------------
  var WPLF = window.WPLF;

  if (WPLF && WPLF.manager && WPLF.manager.forms) {
    var forms = WPLF.manager.forms;

    var defaultSuccessCallback = function (wplfForm, params) {
      var data = params.data && params.data.data;
      var message = (data && data.message) || '';
      var div = document.createElement('div');

      div.classList.add('wplf-successMessage');
      div.innerHTML = message;

      wplfForm.form.appendChild(div);
      wplfForm.form.classList.add('submitted');
    };

    var defaultErrorCallback = function (wplfForm, params) {
      var error = params.error;
      var response = params.response;
      var div = document.createElement('div');

      div.classList.add('wplf-errorMessage');
      div.insertAdjacentHTML('afterbegin', error.message);

      if (response && response.data) {
        var d = response.data;
        Object.keys(d).forEach(function (key) {
          if (key === 'requiredFields') {
            var ul = document.createElement('ul');
            d[key].forEach(function (v) {
              var li = document.createElement('li');
              li.innerText = v;
              ul.appendChild(li);
            });
            div.appendChild(ul);
          }
        });
      }

      wplfForm.form.appendChild(div);
      wplfForm.form.classList.add('submitted');
      wplfForm.form.scrollIntoView(true);
    };

    Object.keys(forms).forEach(function (k) {
      var form = forms[k];
      form.addCallback('default', 'success', defaultSuccessCallback);
      form.addCallback('default', 'error', defaultErrorCallback);
    });
  }

  // -------------------------------------------------------------------
  // Viewport height fix for mobile
  // -------------------------------------------------------------------
  function fixVh() {
    var vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', vh + 'px');
  }
  window.addEventListener('resize', fixVh);
  fixVh();
})();
