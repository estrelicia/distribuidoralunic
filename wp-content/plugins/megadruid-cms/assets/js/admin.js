(function () {
	'use strict';

	var toggle = document.querySelector('[data-mdcms-nav-toggle]');
	var rail = document.getElementById('mdcms-app-rail');
	if (toggle && rail) {
		toggle.addEventListener('click', function () {
			var open = document.body.classList.toggle('mdcms-nav-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

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

	function roleCatalog(wrap) {
		try {
			return JSON.parse(wrap.getAttribute('data-roles') || '{}') || {};
		} catch (error) {
			return {};
		}
	}

	function fillRolePick(wrap) {
		var catalog = roleCatalog(wrap);
		var used = {};
		wrap.querySelectorAll('[data-mdcms-chip]').forEach(function (chip) {
			used[chip.getAttribute('data-role') || ''] = true;
		});
		var select = wrap.querySelector('[data-mdcms-role-pick]');
		if (!select) {
			return;
		}
		var placeholder = (typeof mdcmsAdmin !== 'undefined' && mdcmsAdmin.rolePickPlaceholder)
			? mdcmsAdmin.rolePickPlaceholder
			: '';
		select.innerHTML = '';
		var first = document.createElement('option');
		first.value = '';
		first.textContent = placeholder;
		select.appendChild(first);
		Object.keys(catalog).forEach(function (slug) {
			if (used[slug]) {
				return;
			}
			var option = document.createElement('option');
			option.value = slug;
			option.textContent = catalog[slug];
			select.appendChild(option);
		});
		select.disabled = select.options.length <= 1;
	}

	function addRoleChip(wrap, slug) {
		var catalog = roleCatalog(wrap);
		if (!slug || !catalog[slug] || wrap.querySelector('[data-role="' + slug + '"]')) {
			return;
		}
		var chips = wrap.querySelector('[data-mdcms-chips]');
		var name = wrap.getAttribute('data-input-name') || '';
		if (!chips || name === '') {
			return;
		}
		var chip = document.createElement('span');
		chip.className = 'mdcms-chip';
		chip.setAttribute('data-mdcms-chip', '');
		chip.setAttribute('data-role', slug);
		var label = document.createElement('span');
		label.className = 'mdcms-chip__label';
		label.textContent = catalog[slug];
		var remove = document.createElement('button');
		remove.type = 'button';
		remove.className = 'mdcms-chip__remove';
		remove.setAttribute('data-mdcms-chip-remove', '');
		remove.setAttribute('aria-label', ((typeof mdcmsAdmin !== 'undefined' && mdcmsAdmin.roleRemove) ? mdcmsAdmin.roleRemove : '') + ' ' + catalog[slug]);
		remove.textContent = '×';
		var input = document.createElement('input');
		input.type = 'hidden';
		input.name = name;
		input.value = slug;
		chip.appendChild(label);
		chip.appendChild(remove);
		chip.appendChild(input);
		chips.appendChild(chip);
		fillRolePick(wrap);
	}

	document.querySelectorAll('[data-mdcms-menu-hide]').forEach(function (box) {
		box.addEventListener('change', function () {
			var row = box.closest('.mdcms-menu-row');
			var add = row ? row.querySelector('[data-mdcms-role-add]') : null;
			if (!add) {
				return;
			}
			if (box.checked) {
				add.hidden = false;
				fillRolePick(add);
				return;
			}
			add.hidden = true;
			add.querySelectorAll('[data-mdcms-chip]').forEach(function (chip) {
				chip.remove();
			});
			fillRolePick(add);
		});
	});

	document.querySelectorAll('[data-mdcms-role-add]').forEach(function (wrap) {
		fillRolePick(wrap);
		wrap.addEventListener('click', function (event) {
			var addBtn = event.target.closest('[data-mdcms-role-add-btn]');
			if (addBtn) {
				event.preventDefault();
				var select = wrap.querySelector('[data-mdcms-role-pick]');
				if (select && select.value) {
					addRoleChip(wrap, select.value);
				}
				return;
			}
			var removeBtn = event.target.closest('[data-mdcms-chip-remove]');
			if (removeBtn) {
				event.preventDefault();
				var chip = removeBtn.closest('[data-mdcms-chip]');
				if (chip) {
					chip.remove();
				}
				fillRolePick(wrap);
			}
		});
	});
})();
