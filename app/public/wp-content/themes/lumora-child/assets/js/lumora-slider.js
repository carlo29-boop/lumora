/* ============================================================
   LUMORA — Hero slider (vanilla JS, no dependencies)
   Progressive enhancement over Elementor containers:
   - .lumora-slider  : slider region (Elementor container)
   - .lumora-slide   : individual slides (Elementor containers)
   - .lumora-slider-prev / .lumora-slider-next / [data-slider-dot]
   Behaviour: fade rotation every 5.5s, pause on hover/focus,
   stop after manual navigation, keyboard arrows, touch swipe,
   full ARIA wiring, autoplay disabled for prefers-reduced-motion
   and inside the Elementor editor (slides stay stacked there).
   ============================================================ */
(function () {
	'use strict';

	var AUTOPLAY_MS = 5500;
	var SWIPE_PX = 40;

	function initSlider(slider) {
		// NOTE: slides are NOT direct children — Elementor wraps boxed
		// container content in an extra .e-con-inner div. A ':scope >'
		// child selector matches nothing here, so use descendants.
		var slides = Array.prototype.slice.call(slider.querySelectorAll('.lumora-slide'));
		if (slides.length < 2) {
			return;
		}

		var prev = slider.querySelector('[data-slider-prev]');
		var next = slider.querySelector('[data-slider-next]');
		var dots = Array.prototype.slice.call(slider.querySelectorAll('[data-slider-dot]'));
		var current = 0;
		var timer = null;
		var userStopped = false;
		var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		slider.setAttribute('role', 'region');
		slider.setAttribute('aria-roledescription', 'carousel');
		if (!slider.getAttribute('aria-label')) {
			slider.setAttribute('aria-label', 'Featured properties');
		}

		slides.forEach(function (slide, i) {
			slide.setAttribute('aria-roledescription', 'slide');
			slide.setAttribute('aria-label', (i + 1) + ' of ' + slides.length);
		});

		function show(index, focusSlide) {
			current = (index + slides.length) % slides.length;
			slides.forEach(function (slide, i) {
				var active = i === current;
				slide.classList.toggle('is-active', active);
				if (active) {
					slide.removeAttribute('aria-hidden');
				} else {
					slide.setAttribute('aria-hidden', 'true');
				}
			});
			dots.forEach(function (dot, i) {
				if (i === current) {
					dot.setAttribute('aria-current', 'true');
				} else {
					dot.removeAttribute('aria-current');
				}
			});
			if (focusSlide) {
				slides[current].setAttribute('tabindex', '-1');
				slides[current].focus({ preventScroll: true });
			}
		}

		function stopAutoplay() {
			if (timer) {
				window.clearInterval(timer);
				timer = null;
			}
		}

		function startAutoplay() {
			if (timer || userStopped || reduceMotion) {
				return;
			}
			timer = window.setInterval(function () {
				show(current + 1);
			}, AUTOPLAY_MS);
		}

		function go(index, manual) {
			show(index);
			if (manual) {
				// Manual navigation stops automatic rotation for this page view.
				userStopped = true;
				stopAutoplay();
			}
		}

		if (prev) {
			prev.addEventListener('click', function () {
				go(current - 1, true);
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				go(current + 1, true);
			});
		}
		dots.forEach(function (dot, i) {
			dot.addEventListener('click', function () {
				go(i, true);
			});
		});

		// Pause while hovered or focused, resume on leave.
		slider.addEventListener('pointerenter', stopAutoplay);
		slider.addEventListener('pointerleave', startAutoplay);
		slider.addEventListener('focusin', stopAutoplay);
		slider.addEventListener('focusout', startAutoplay);

		// Keyboard: left/right arrows move between slides.
		slider.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft') {
				e.preventDefault();
				go(current - 1, true);
			} else if (e.key === 'ArrowRight') {
				e.preventDefault();
				go(current + 1, true);
			}
		});

		// Touch swipe.
		var touchX = null;
		slider.addEventListener('touchstart', function (e) {
			if (e.touches.length === 1) {
				touchX = e.touches[0].clientX;
			}
		}, { passive: true });
		slider.addEventListener('touchend', function (e) {
			if (touchX === null || e.changedTouches.length !== 1) {
				return;
			}
			var dx = e.changedTouches[0].clientX - touchX;
			touchX = null;
			if (Math.abs(dx) >= SWIPE_PX) {
				go(current + (dx < 0 ? 1 : -1), true);
			}
		}, { passive: true });

		// Pause when the tab is hidden.
		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				stopAutoplay();
			} else {
				startAutoplay();
			}
		});

		show(0);
		startAutoplay();
	}

	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	ready(function () {
		// Inside the Elementor editor keep slides stacked and static.
		if (document.body.classList.contains('elementor-editor-active')) {
			return;
		}
		Array.prototype.forEach.call(document.querySelectorAll('.lumora-slider'), initSlider);
	});
})();
