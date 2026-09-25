(function () {
	var button = document.querySelector('.lunic-menu-btn');
	var panel = document.getElementById('lunic-panel');
	if (button && panel) {
		button.addEventListener('click', function () {
			var open = panel.classList.toggle('is-open');
			button.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}
	var mega = document.getElementById('lunic-mega');
	var cats = document.getElementById('lunic-panel-cats');
	document.querySelectorAll('a[href="#menu_categorias"]').forEach(function (link) {
		link.addEventListener('click', function (event) {
			event.preventDefault();
			if (window.matchMedia('(max-width: 781px)').matches) {
				if (cats) cats.hidden = !cats.hidden;
				if (panel && !panel.classList.contains('is-open') && button) button.click();
				return;
			}
			if (mega) mega.hidden = !mega.hidden;
		});
	});
	var footBtn = document.querySelector('.lunic-footer__toggle');
	var footMenu = document.getElementById('lunic-footer-menu');
	if (footBtn && footMenu) {
		footBtn.addEventListener('click', function () {
			var open = footMenu.classList.toggle('is-open');
			footBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
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
		var i = 0;
		var slides = track.children.length;
		setInterval(function () {
			i = (i + 1) % slides;
			track.scrollTo({ left: i * track.clientWidth, behavior: 'smooth' });
		}, 5000);
	});
})();
