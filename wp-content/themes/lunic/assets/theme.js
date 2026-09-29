(function () {
	document.querySelectorAll('.single-product .elementor-widget-woocommerce-product-short-description').forEach(function (desc) {
		var section = desc.closest('.elementor-inner-section');
		var grid = section && section.querySelector(':scope > .elementor-container');
		if (grid && desc.parentElement !== grid) grid.appendChild(desc);
	});
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
	var buybar = document.querySelector('.lunic-buybar');
	var buy = document.querySelector('.lunic-buybar__btn');
	var formBtn = document.querySelector('form.cart .single_add_to_cart_button');
	if (buy && formBtn) {
		buy.addEventListener('click', function () {
			formBtn.click();
		});
	}
	if (buybar && formBtn && 'IntersectionObserver' in window) {
		var priceSlot = buybar.querySelector('.lunic-buybar__price');
		var initialPrice = priceSlot ? priceSlot.innerHTML : '';
		var syncPrice = function () {
			if (!priceSlot) return;
			var live = document.querySelector('.woocommerce-variation-price .price');
			priceSlot.innerHTML = live && live.textContent.trim() ? live.innerHTML : initialPrice;
		};
		var form = formBtn.closest('form');
		if (form) {
			new MutationObserver(syncPrice).observe(form, { childList: true, subtree: true, characterData: true });
		}
		new IntersectionObserver(function (entries) {
			buybar.classList.toggle('is-hidden', entries[0].isIntersecting);
		}, { rootMargin: '0px 0px 0px 0px' }).observe(formBtn);
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
