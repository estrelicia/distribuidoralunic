(function () {
	var form = document.getElementById('lunic-mega-form');
	var field = document.getElementById('lunic-mega-data');
	if (!form || !field) {
		return;
	}

	var dragged = null;

	function sync() {
		var columns = [[], [], []];
		form.querySelectorAll('[data-list]').forEach(function (list) {
			var key = list.getAttribute('data-list');
			if (key === 'pool') {
				return;
			}
			var index = parseInt(key, 10);
			if (index < 0 || index > 2) {
				return;
			}
			list.querySelectorAll('[data-id]').forEach(function (item) {
				columns[index].push(parseInt(item.getAttribute('data-id'), 10));
			});
		});
		field.value = JSON.stringify({ columns: columns });
	}

	function place(list, event) {
		var items = Array.prototype.slice.call(list.querySelectorAll('[data-id]:not(.is-dragging)'));
		var next = null;
		var offset = Number.NEGATIVE_INFINITY;
		items.forEach(function (item) {
			var box = item.getBoundingClientRect();
			var delta = event.clientY - box.top - box.height / 2;
			if (delta < 0 && delta > offset) {
				offset = delta;
				next = item;
			}
		});
		if (next) {
			list.insertBefore(dragged, next);
		} else {
			list.appendChild(dragged);
		}
	}

	form.addEventListener('dragstart', function (event) {
		var item = event.target.closest('[data-id]');
		if (!item) {
			return;
		}
		dragged = item;
		item.classList.add('is-dragging');
		event.dataTransfer.effectAllowed = 'move';
		event.dataTransfer.setData('text/plain', item.getAttribute('data-id'));
	});

	form.addEventListener('dragend', function () {
		if (dragged) {
			dragged.classList.remove('is-dragging');
		}
		dragged = null;
		form.querySelectorAll('.is-over').forEach(function (list) {
			list.classList.remove('is-over');
		});
		sync();
	});

	form.addEventListener('dragover', function (event) {
		var list = event.target.closest('[data-list]');
		if (!list || !dragged) {
			return;
		}
		event.preventDefault();
		form.querySelectorAll('.is-over').forEach(function (other) {
			if (other !== list) {
				other.classList.remove('is-over');
			}
		});
		list.classList.add('is-over');
		place(list, event);
	});

	form.addEventListener('submit', sync);
	sync();
})();
