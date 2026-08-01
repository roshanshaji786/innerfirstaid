/**
 * Inner First Aid — front-end behaviour for IFA widgets.
 * - Lead forms: AJAX submit with validation, loading/success/error states.
 * - Program card gender picker.
 * - Cookie consent banner + consent-gated analytics (GA4 / Meta Pixel).
 */
(function () {
	'use strict';

	var CFG = window.IFA_FRONTEND || { ajaxUrl: '', nonce: '' };

	/* ------------------------------------------------------------------ *
	 * Lead forms
	 * ------------------------------------------------------------------ */

	function leadFormSubmit(form) {
		var input = form.querySelector('input[name="email"]');
		var status = form.querySelector('[data-ifa-lead-status]');
		var btn = form.querySelector('[data-ifa-lead-submit]');
		var email = (input ? input.value : '').trim();
		var lang = form.getAttribute('data-ifa-lang') || 'en';
		var source = form.getAttribute('data-ifa-source') || 'landing_page';

		function setStatus(type, text) {
			if (!status) return;
			status.textContent = text || '';
			status.className = 'ifa-lead-form__status is-visible' + (type ? ' is-' + type : '');
		}

		function clearStatus() {
			if (!status) return;
			status.textContent = '';
			status.className = 'ifa-lead-form__status';
		}

		// Client-side validation mirrors the server.
		if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
			setStatus('error', form.getAttribute('data-ifa-invalid') || 'Please enter a valid email address.');
			if (input) input.focus();
			return;
		}

		clearStatus();
		if (btn) {
			btn.disabled = true;
			btn.dataset.label = btn.dataset.label || btn.textContent;
			btn.textContent = '…';
		}

		var body = new URLSearchParams();
		body.set('action', 'ifa_submit_lead');
		body.set('nonce', CFG.nonce || '');
		body.set('email', email);
		body.set('lang', lang);
		body.set('source', source);
		var hp = form.querySelector('input[name="company_website"]');
		if (hp && hp.value) {
			body.set('company_website', hp.value);
		}

		fetch(CFG.ajaxUrl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		})
			.then(function (res) {
				return res.json().then(function (data) {
					return { ok: res.ok, data: data };
				});
			})
			.then(function (result) {
				if (result.ok && result.data && result.data.success) {
					setStatus('success', form.getAttribute('data-ifa-success') || 'Sent!');
					if (input) input.value = '';
				} else {
					setStatus('error', form.getAttribute('data-ifa-error') || 'Something went wrong. Please try again.');
				}
			})
			.catch(function () {
				setStatus('error', form.getAttribute('data-ifa-error') || 'Something went wrong. Please try again.');
			})
			.finally(function () {
				if (btn) {
					btn.disabled = false;
					btn.textContent = btn.dataset.label || btn.textContent;
				}
			});
	}

	document.addEventListener('submit', function (e) {
		var form = e.target.closest('[data-ifa-lead-form]');
		if (!form) return;
		e.preventDefault();
		leadFormSubmit(form);
	});

	/* ------------------------------------------------------------------ *
	 * Program card gender picker
	 * ------------------------------------------------------------------ */

	document.addEventListener('click', function (e) {
		var toggle = e.target.closest('[data-ifa-gender-toggle]');
		if (!toggle) return;

		var wrap = toggle.closest('[data-ifa-gender]');
		if (!wrap) return;

		var options = wrap.querySelector('[data-ifa-gender-options]');
		if (!options) return;

		options.hidden = !options.hidden;
		if (!options.hidden) {
			options.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}
	});

	/* ------------------------------------------------------------------ *
	 * Cookie consent + analytics
	 * ------------------------------------------------------------------ */

	function loadAnalytics() {
		var banner = document.querySelector('[data-ifa-cookie]');
		if (!banner) return;
		var ga = banner.getAttribute('data-ifa-ga');
		var pixel = banner.getAttribute('data-ifa-pixel');

		if (ga) {
			var s = document.createElement('script');
			s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(ga);
			s.async = true;
			document.head.appendChild(s);

			var inline = document.createElement('script');
			inline.textContent =
				'window.dataLayer=window.dataLayer||[];' +
				'function gtag(){dataLayer.push(arguments);}' +
				"gtag('js',new Date());" +
				"gtag('config','" + ga.replace(/[^a-zA-Z0-9-]/g, '') + "',{anonymize_ip:true});";
			document.head.appendChild(inline);
		}

		if (pixel) {
			var p = document.createElement('script');
			p.textContent =
				"!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};" +
				"if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;" +
				"t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}" +
				"(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');" +
				"fbq('init','" + pixel.replace(/[^0-9]/g, '') + "');fbq('track','PageView');";
			document.head.appendChild(p);
		}
	}

	function initCookieBanner() {
		var banner = document.querySelector('[data-ifa-cookie]');
		if (!banner) return;

		var acceptBtn = banner.querySelector('[data-ifa-cookie-accept-btn]');
		var declineBtn = banner.querySelector('[data-ifa-cookie-decline-btn]');
		var textEl = banner.querySelector('.ifa-cookie__text');

		if (acceptBtn) acceptBtn.textContent = banner.getAttribute('data-ifa-cookie-accept') || 'Accept';
		if (declineBtn) declineBtn.textContent = banner.getAttribute('data-ifa-cookie-decline') || 'Decline';
		if (textEl) textEl.textContent = banner.getAttribute('data-ifa-cookie-text') || '';

		var storageKey = 'ifa_cookie_consent';
		var saved = null;
		try {
			saved = window.localStorage.getItem(storageKey);
		} catch (err) {
			saved = null;
		}

		var hide = function () {
			banner.classList.add('is-hidden');
			window.setTimeout(function () {
				banner.hidden = true;
			}, 400);
		};

		var accept = function () {
			try {
				window.localStorage.setItem(storageKey, 'granted');
			} catch (err) {}
			loadAnalytics();
			hide();
		};

		var decline = function () {
			try {
				window.localStorage.setItem(storageKey, 'denied');
			} catch (err) {}
			hide();
		};

		if (acceptBtn) acceptBtn.addEventListener('click', accept);
		if (declineBtn) declineBtn.addEventListener('click', decline);

		if (saved === 'granted') {
			banner.hidden = true;
			loadAnalytics();
			return;
		}

		if (saved === 'denied') {
			banner.hidden = true;
			return;
		}

		// Show the banner after a short delay.
		window.setTimeout(function () {
			banner.hidden = false;
		}, 1200);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCookieBanner);
	} else {
		initCookieBanner();
	}
})();
