(function () {
	var form = document.querySelector('.lunic-search');
	if (!form || !window.lunicSearch) return;
	var input = form.querySelector('.lunic-search__input');
	var panel = form.querySelector('.lunic-search__panel');
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
			if (item.image) {
				var img = document.createElement('img');
				img.src = item.image;
				img.alt = '';
				link.appendChild(img);
			}
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
		if (q.length < 1) {
			close();
			return;
		}
		timer = setTimeout(function () {
			fetch(lunicSearch.endpoint + '?q=' + encodeURIComponent(q))
				.then(function (r) { return r.json(); })
				.then(function (data) { render(data.items || []); })
				.catch(close);
		}, 200);
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') close();
	});
	document.addEventListener('click', function (event) {
		if (!form.contains(event.target)) close();
	});
})();
