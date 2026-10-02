(function () {
	'use strict';

	if (typeof mdcmsBlockEditor === 'undefined' || mdcmsBlockEditor.mode === 'wordpress') {
		return;
	}

	var markup = mdcmsBlockEditor.markup || '';
	if (!markup) {
		return;
	}

	var selectors = [
		'.edit-post-fullscreen-mode-close',
		'.editor-header__back-button'
	];

	function applyToButton(button) {
		if (!button || button.querySelector('.mdcms-exit-icon')) {
			return;
		}
		button.classList.add('mdcms-block-editor-exit');
		var svg = button.querySelector('svg');
		if (svg) {
			svg.style.display = 'none';
		}
		var siteIcon = button.querySelector('.edit-post-fullscreen-mode-close_site-icon');
		if (siteIcon) {
			siteIcon.remove();
		}
		button.insertAdjacentHTML('afterbegin', markup);
	}

	function scan() {
		selectors.forEach(function (selector) {
			document.querySelectorAll(selector).forEach(applyToButton);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', scan);
	} else {
		scan();
	}

	var observer = new MutationObserver(scan);
	observer.observe(document.documentElement, { childList: true, subtree: true });
})();
