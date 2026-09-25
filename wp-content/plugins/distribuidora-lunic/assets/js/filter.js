(function () {
	var root = document.querySelector('.lunic-filter');
	if (!root || !window.lunicFilter) return;
	var toggle = root.querySelector('.lunic-filter__toggle');

	function shopUrl(term) {
		var url = new URL(lunicFilter.shop, window.location.origin);
		if (term) url.searchParams.set(lunicFilter.arg, term);
		return url;
	}

	function replaceLoop(html) {
		var doc = new DOMParser().parseFromString(html, 'text/html');
		['.products', '.woocommerce-result-count', '.woocommerce-pagination', '.woocommerce-info'].forEach(function (sel) {
			var next = doc.querySelector(sel);
			var current = document.querySelector(sel);
			if (next && current) current.replaceWith(next);
		});
		document.querySelectorAll('.lunic-filter a').forEach(function (link) {
			link.classList.toggle('is-active', link.getAttribute('data-term') === String(lunicFilter.active || 0));
		});
	}

	function load(term, push) {
		var url = shopUrl(term);
		lunicFilter.active = term;
		fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
			.then(function (r) { return r.text(); })
			.then(function (html) {
				replaceLoop(html);
				if (push) history.pushState({ lunic_cat: term }, '', url);
			});
	}

	root.addEventListener('click', function (event) {
		var link = event.target.closest('a[data-term]');
		if (!link) return;
		event.preventDefault();
		load(link.getAttribute('data-term'), true);
		root.classList.remove('is-open');
		if (toggle) toggle.setAttribute('aria-expanded', 'false');
	});

	if (toggle) {
		toggle.addEventListener('click', function () {
			var open = root.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	window.addEventListener('popstate', function () {
		var params = new URLSearchParams(window.location.search);
		load(params.get(lunicFilter.arg) || '0', false);
	});
})();
