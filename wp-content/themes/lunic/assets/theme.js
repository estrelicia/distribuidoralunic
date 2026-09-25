(function () {
	var button = document.querySelector('.lunic-menu-btn');
	var panel = document.getElementById('lunic-panel');
	if (button && panel) {
		button.addEventListener('click', function () {
			var open = panel.classList.toggle('is-open');
			button.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}
	var buy = document.querySelector('.lunic-buybar__btn');
	if (buy) {
		buy.addEventListener('click', function () {
			var formBtn = document.querySelector('form.cart .single_add_to_cart_button');
			if (formBtn) formBtn.click();
		});
	}
	document.querySelectorAll('.lunic-slider').forEach(function (slider) {
		var track = slider.querySelector('.lunic-slider__track');
		if (!track) return;
		var prev = document.createElement('button');
		var next = document.createElement('button');
		prev.type = next.type = 'button';
		prev.className = next.className = 'lunic-slider__btn';
		prev.textContent = 'Anterior';
		next.textContent = 'Siguiente';
		prev.style.left = '0.5rem';
		next.style.right = '0.5rem';
		slider.appendChild(prev);
		slider.appendChild(next);
		function step(dir) { track.scrollBy({ left: dir * track.clientWidth, behavior: 'smooth' }); }
		prev.addEventListener('click', function () { step(-1); });
		next.addEventListener('click', function () { step(1); });
	});
})();
