(function () {
	if (!window.lunicSearch) return;

	function setup(form) {
		var input = form.querySelector('.lunic-search__input');
		var panel = form.querySelector('.lunic-search__panel');
		if (!input || !panel) return;
		var timer = null;

		function close() {
			panel.hidden = true;
			panel.innerHTML = '';
		}

		function render(items) {
			panel.innerHTML = '';
			if (!items.length) {
				close();
				return;
			}
			items.forEach(function (item) {
				var link = document.createElement('a');
				link.className = 'lunic-search__item';
				link.href = item.url;
				var thumb = document.createElement(item.image ? 'img' : 'span');
				thumb.className = 'lunic-search__thumb';
				if (item.image) {
					thumb.src = item.image;
					thumb.alt = '';
					thumb.loading = 'lazy';
				}
				link.appendChild(thumb);
				var text = document.createElement('span');
				text.innerHTML = '<strong></strong><span class="lunic-search__meta"></span><span class="lunic-search__meta"></span>';
				text.querySelector('strong').textContent = item.title;
				text.querySelectorAll('.lunic-search__meta')[0].textContent = item.category;
				text.querySelectorAll('.lunic-search__meta')[1].textContent = item.price;
				link.appendChild(text);
				panel.appendChild(link);
			});
			panel.hidden = false;
		}

		input.addEventListener('input', function () {
			var q = input.value.trim();
			clearTimeout(timer);
			if (q.length < 2) {
				close();
				return;
			}
			timer = setTimeout(function () {
				fetch(lunicSearch.endpoint + '?q=' + encodeURIComponent(q))
					.then(function (r) {
						if (!r.ok) {
							close();
							return null;
						}
						return r.json();
					})
					.then(function (data) {
						if (data && input.value.trim() === q) render(data.items || []);
					})
					.catch(close);
			}, 300);
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') close();
		});
		document.addEventListener('click', function (event) {
			if (!form.contains(event.target)) close();
		});
	}

	document.querySelectorAll('.lunic-search').forEach(setup);
})();
