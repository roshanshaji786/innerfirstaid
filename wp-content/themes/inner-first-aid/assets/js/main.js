/**
 * Inner First Aid — theme front-end behaviour.
 * (Header scroll shadow, mobile menu, smooth anchor scrolling, language switch.)
 */
(function () {
	'use strict';

	var header = document.querySelector('[data-ifa-header]');
	var toggle = document.querySelector('[data-ifa-nav-toggle]');
	var nav = document.querySelector('[data-ifa-nav]');

	if (header) {
		var onScroll = function () {
			header.classList.toggle('is-scrolled', window.scrollY > 10);
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	if (toggle && nav) {
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});

		// Close the mobile menu when a link inside it is clicked.
		nav.addEventListener('click', function (e) {
			if (e.target.closest('a')) {
				nav.classList.remove('is-open');
				toggle.setAttribute('aria-expanded', 'false');
			}
		});
	}

	// Smooth-scroll CTA: the header CTA points at #programs which may not exist yet
	// on a freshly built page — scroll into view of the first [data-ifa-section=programs].
	document.addEventListener('click', function (e) {
		var link = e.target.closest('a[href="#programs"]');
		if (!link) {
			return;
		}
		var target = document.getElementById('programs') || document.querySelector('[data-ifa-section="programs"]');
		if (!target) {
			return;
		}
		e.preventDefault();
		var top = target.getBoundingClientRect().top + window.pageYOffset - 72;
		window.scrollTo({ top: top, behavior: 'smooth' });
	});
})();
