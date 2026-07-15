(function($) {
	'use strict';

	if (typeof $ === 'undefined') {
		console.error('jQuery is required for Usein kysyttyä module');
		return;
	}

	// Function to handle accordion toggle
	function handleAccordionToggle(e) {
		e.preventDefault();
		e.stopPropagation();
		
		var $button = $(this);
		var $item = $button.closest('.fl-usein-kysyttya-item');
		openAccordionItem($item, true);
	}

	function openAccordionItem($item, toggleCurrent) {
		var $button = $item.find('.fl-usein-kysyttya-question').first();
		var $answer = $item.find('.fl-usein-kysyttya-answer').first();
		
		if ($button.length === 0 || $answer.length === 0) {
			return;
		}
		
		var isActive = $button.hasClass('active');
		
		// Close all other items in the same module
		var $module = $button.closest('.fl-usein-kysyttya');
		$module.find('.fl-usein-kysyttya-question').not($button).removeClass('active');
		$module.find('.fl-usein-kysyttya-answer').not($answer).removeClass('open').attr('aria-hidden', 'true');
		$module.find('.fl-usein-kysyttya-question').not($button).attr('aria-expanded', 'false');
		
		// Toggle current item
		if (toggleCurrent && isActive) {
			$button.removeClass('active').attr('aria-expanded', 'false');
			$answer.removeClass('open').attr('aria-hidden', 'true');
		} else {
			$button.addClass('active').attr('aria-expanded', 'true');
			$answer.addClass('open').attr('aria-hidden', 'false');
		}
	}

	function openQuestionFromHash() {
		var hash = window.location.hash ? window.location.hash.slice(1) : '';
		if (!hash) {
			return;
		}

		var targetEl = document.getElementById(hash);
		if (!targetEl) {
			return;
		}

		var $item = $(targetEl).hasClass('fl-usein-kysyttya-item')
			? $(targetEl)
			: $(targetEl).closest('.fl-usein-kysyttya-item');

		if ($item.length === 0) {
			return;
		}

		openAccordionItem($item, false);
	}

	// Initialize when DOM is ready
	function initAccordion() {
		// Bind directly to existing elements
		$('.fl-usein-kysyttya-question').off('click.accordion').on('click.accordion', handleAccordionToggle);
	}

	// Initialize on document ready
	$(document).ready(function() {
		initAccordion();
		openQuestionFromHash();
	});

	// Use event delegation for dynamically added content
	$(document).on('click', '.fl-usein-kysyttya-question', handleAccordionToggle);

	// Reinitialize on AJAX content load (for partial refresh)
	$(document).on('fl-builder.partial-refresh', function() {
		initAccordion();
		openQuestionFromHash();
	});

	$(window).on('hashchange', function() {
		openQuestionFromHash();
	});

	// Also initialize after a short delay to catch any late-loading content
	setTimeout(function() {
		initAccordion();
		openQuestionFromHash();
	}, 100);

})(jQuery);

