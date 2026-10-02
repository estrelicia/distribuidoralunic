(function () {
	'use strict';

	function bindMediaField(root) {
		if (!root || typeof wp === 'undefined' || !wp.media) {
			return;
		}
		var input = root.querySelector('[data-mdcms-media-url]');
		var preview = root.querySelector('[data-mdcms-media-preview]');
		var choose = root.querySelector('[data-mdcms-media-choose]');
		var clear = root.querySelector('[data-mdcms-media-clear]');
		if (!input || !choose) {
			return;
		}

		var frame;
		choose.addEventListener('click', function (event) {
			event.preventDefault();
			if (frame) {
				frame.open();
				return;
			}
			frame = wp.media({
				title: choose.getAttribute('data-title') || '',
				library: { type: 'image' },
				multiple: false,
				button: { text: choose.getAttribute('data-button') || '' }
			});
			frame.on('select', function () {
				var attachment = frame.state().get('selection').first();
				if (!attachment) {
					return;
				}
				var data = attachment.toJSON();
				input.value = data.url || '';
				if (preview) {
					preview.src = data.url || '';
					preview.hidden = !data.url;
				}
			});
			frame.open();
		});

		if (clear) {
			clear.addEventListener('click', function (event) {
				event.preventDefault();
				input.value = '';
				if (preview) {
					preview.src = '';
					preview.hidden = true;
				}
			});
		}
	}

	document.querySelectorAll('[data-mdcms-media]').forEach(bindMediaField);

	var gutenbergSelect = document.getElementById('mdcms_gutenberg_exit_icon');
	var gutenbergCustom = document.querySelector('[data-mdcms-gutenberg-custom]');
	if (gutenbergSelect && gutenbergCustom) {
		var syncGutenberg = function () {
			gutenbergCustom.hidden = gutenbergSelect.value !== 'custom';
		};
		gutenbergSelect.addEventListener('change', syncGutenberg);
		syncGutenberg();
	}

	if (typeof jQuery !== 'undefined' && jQuery.fn.wpColorPicker) {
		jQuery('.mdcms-color').wpColorPicker();
	}

	document.querySelectorAll('[data-mdcms-search]').forEach(function (field) {
		var results = field.parentElement ? field.parentElement.querySelector('.mdcms-search-results') : null;
		var timer;
		field.addEventListener('input', function () {
			window.clearTimeout(timer);
			timer = window.setTimeout(function () {
				var term = field.value.trim();
				if (!results || term.length < 2 || typeof mdcmsAdmin === 'undefined') {
					return;
				}
				var url = mdcmsAdmin.ajax
					+ '?action=mdcms_search_pages&nonce=' + encodeURIComponent(mdcmsAdmin.searchNonce)
					+ '&kind=' + encodeURIComponent(field.getAttribute('data-kind') || 'page')
					+ '&term=' + encodeURIComponent(term);
				fetch(url, { credentials: 'same-origin' })
					.then(function (response) { return response.json(); })
					.then(function (payload) {
						results.innerHTML = '';
						var items = payload && payload.success ? payload.data : [];
						items.forEach(function (item) {
							var button = document.createElement('button');
							button.type = 'button';
							button.className = 'button-link';
							button.textContent = item.title + ' (#' + item.id + ')';
							button.addEventListener('click', function () {
								var target = document.getElementById(field.getAttribute('data-target') || '');
								if (target) {
									target.value = item.id;
								}
								results.hidden = true;
							});
							var row = document.createElement('li');
							row.appendChild(button);
							results.appendChild(row);
						});
						results.hidden = items.length === 0;
					});
			}, 250);
		});
	});
})();
