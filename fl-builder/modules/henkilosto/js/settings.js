(function ($) {
	'use strict';

	function settingsForm() {
		return $('.fl-builder-settings:visible');
	}

	function importMeta($form) {
		var $wrap = $form.find('.mh-henkilosto-import-actions');
		return {
			nonce: $wrap.data('nonce') || '',
			confirm: $wrap.data('confirm') || 'Korvataanko kaikki nykyiset henkilöt tuoduilla riveillä?',
			working: $wrap.data('working') || 'Tuodaan…',
			done: $wrap.data('done') || 'Tuonti valmis — tallennetaan…',
			error: $wrap.data('error') || 'Tuonti epäonnistui.',
			empty: $wrap.data('empty') || 'Liitä ensin CSV-sisältö bulk-kenttään.',
			ajaxUrl: $wrap.data('ajax-url') || (typeof ajaxurl !== 'undefined' ? ajaxurl : ''),
		};
	}

	function setStatus($form, text, isError) {
		var $status = $form.find('.mh-henkilosto-import-status');
		$status.text(text || '');
		$status.css('color', isError ? '#b32d2e' : '');
	}

	function personDefaults() {
		var defaults = {};
		if (
			typeof FLBuilderSettingsConfig !== 'undefined' &&
			FLBuilderSettingsConfig.defaults &&
			FLBuilderSettingsConfig.defaults.forms &&
			FLBuilderSettingsConfig.defaults.forms.henkilosto_person_form
		) {
			defaults = $.extend(true, {}, FLBuilderSettingsConfig.defaults.forms.henkilosto_person_form);
		}
		if (!defaults.languages) {
			defaults.languages = ['fi'];
		}
		return defaults;
	}

	function ensureLanguages(person) {
		if (!person.languages || (Array.isArray(person.languages) && !person.languages.length)) {
			person.languages = ['fi'];
		}
		return person;
	}

	function personRows($form) {
		return $form.find('tr.fl-builder-field-multiple[data-field="persons"]');
	}

	function personsTable($form) {
		var $table = $form.find('#fl-field-persons');
		if ($table.length) {
			return $table;
		}
		return personRows($form).first().closest('table, .fl-field');
	}

	/**
	 * Clone a person row without using BB's Add click handler
	 * (Add crashes when zero rows remain — _updateRepeaterFormState).
	 */
	function clonePersonRow($source) {
		var $clone = $source.clone(false, false);
		$clone.find('.fl-form-field-preview-text').html('');
		$clone.find('.fl-form-field-before, .fl-form-field-after').remove();
		$clone.find('.picker-mount').empty();
		$clone.find('.fl-color-picker-color').css('background-color', 'transparent').addClass('fl-color-picker-empty');
		return $clone;
	}

	function syncRepeater($form) {
		var $table = personsTable($form);
		if (!$table.length || !$table.attr('id')) {
			return;
		}
		if (typeof FLBuilder === 'undefined') {
			return;
		}
		try {
			if (typeof FLBuilder._renumberFields === 'function') {
				FLBuilder._renumberFields($table);
			}
			if (typeof FLBuilder._initMultipleFields === 'function') {
				FLBuilder._initMultipleFields();
			}
			if (typeof FLBuilder._updateRepeaterFormState === 'function') {
				FLBuilder._updateRepeaterFormState($table);
			}
		} catch (err) {
			// BB state sync is best-effort; values are already in the inputs.
			if (window.console && console.warn) {
				console.warn('Henkilöstö import: repeater sync skipped', err);
			}
		}
	}

	/**
	 * Replace the persons repeater with imported rows, then save the module.
	 */
	function applyPersons($form, persons, meta) {
		var defaults = personDefaults();
		var $rows = personRows($form);

		if (!$rows.length) {
			setStatus($form, meta.error + ' (ei henkilö-rivejä — lisää ensin yksi henkilö Yleistä-välilehdellä)', true);
			return;
		}

		// Grow by cloning (never delete the last row before cloning).
		while (personRows($form).length < persons.length) {
			var $last = personRows($form).last();
			$last.after(clonePersonRow($last));
		}

		// Shrink extras from the end.
		while (personRows($form).length > persons.length) {
			personRows($form).last().remove();
		}

		syncRepeater($form);

		persons.forEach(function (person, index) {
			person = ensureLanguages($.extend(true, {}, defaults, person));
			var $row = personRows($form).eq(index);
			if (!$row.length) {
				return;
			}
			var $input = $row.find('.fl-form-field input[type="hidden"]');
			if (!$input.length) {
				$input = $row.find('.fl-form-field input').first();
			}
			$input.val(JSON.stringify(person)).trigger('change');
			$row.find('.fl-form-field-preview-text').text(person.name || '');
		});

		syncRepeater($form);

		$form.find('textarea[name="bulk_import"]').val('');
		setStatus($form, meta.done, false);

		window.setTimeout(function () {
			var $save = $form.find('.fl-builder-settings-save').first();
			if ($save.length) {
				$save.trigger('click');
			}
		}, 150);
	}

	function runImport(e) {
		e.preventDefault();
		e.stopPropagation();

		var $form = settingsForm();
		var meta = importMeta($form);
		var text = $.trim($form.find('textarea[name="bulk_import"]').val() || '');

		if (!text) {
			setStatus($form, meta.empty, true);
			return;
		}

		if (!window.confirm(meta.confirm)) {
			return;
		}

		var $btn = $form.find('.mh-henkilosto-import-submit');
		$btn.prop('disabled', true);
		setStatus($form, meta.working, false);

		$.ajax({
			url: meta.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: {
				action: 'mh_henkilosto_parse_import',
				nonce: meta.nonce,
				text: text,
			},
		})
			.done(function (res) {
				try {
					if (!res || !res.success || !res.data || !res.data.persons) {
						var msg = (res && res.data && res.data.message) ? res.data.message : meta.error;
						setStatus($form, msg, true);
						return;
					}
					applyPersons($form, res.data.persons, meta);
				} catch (err) {
					setStatus($form, meta.error, true);
					if (window.console && console.error) {
						console.error('Henkilöstö import failed', err);
					}
				}
			})
			.fail(function (xhr) {
				var msg = meta.error;
				if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
					msg = xhr.responseJSON.data.message;
				}
				setStatus($form, msg, true);
			})
			.always(function () {
				$btn.prop('disabled', false);
			});
	}

	FLBuilder.registerModuleHelper('henkilosto', {
		init: function () {
			var $form = settingsForm();
			$form.off('click.mhHenkilostoImport', '.mh-henkilosto-import-submit');
			$form.on('click.mhHenkilostoImport', '.mh-henkilosto-import-submit', runImport);
		},
	});
})(jQuery);
