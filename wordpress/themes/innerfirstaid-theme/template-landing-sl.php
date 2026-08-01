<?php
/**
 * Template Name: Landing Page (Slovenian)
 *
 * This is the template for the Slovenian landing page.
 *
 * @package InnerFirstAid
 */

get_header();
?>

<!-- Header -->
<header id="site-header" class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 transition-shadow duration-300">
	<div class="max-w-7xl mx-auto px-6 h-[72px] flex items-center justify-between">
		<a href="<?php echo esc_url( home_url( '/sl' ) ); ?>" class="flex items-center gap-3 text-primary" aria-label="Inner First Aid Home">
			<svg width="32" height="32" viewBox="0 0 32 32" fill="none" class="shrink-0" aria-hidden="true">
				<circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="2" />
				<path d="M10 16C10 12 13 9 16 9C19 9 22 12 22 16C22 20 19 23 16 23C13 23 10 20 10 16Z" fill="currentColor" opacity="0.2" />
				<path d="M16 12V20M12 16H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
			</svg>
			<span class="text-lg font-bold tracking-tight">innerfirstaid.com</span>
		</a>

		<button id="menu-toggle" class="lg:hidden p-2 text-text" aria-label="Toggle menu" aria-expanded="false">
			<svg id="menu-icon-hamburger" class="block" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
				<path d="M4 7H20M4 12H20M4 17H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
			</svg>
			<svg id="menu-icon-close" class="hidden" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
				<path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
			</svg>
		</button>

		<nav id="mobile-menu" class="hidden lg:flex absolute lg:relative top-[72px] lg:top-auto left-0 right-0 bg-white lg:bg-transparent flex-col lg:flex-row items-center justify-center lg:justify-end gap-6 p-8 lg:p-0 border-b lg:border-b-0 border-gray-200">
			<a href="#programs" class="bg-primary text-white font-semibold px-5 py-2.5 rounded hover:bg-accent transition-colors">
				Zacni brezplacno
			</a>
		</nav>
	</div>
</header>

<main class="pt-[72px]">
	<!-- Hero Section -->
	<section class="relative min-h-[calc(100vh-72px)] flex items-center overflow-hidden py-20">
		<div class="absolute inset-0 z-0">
			<img
				src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=2400&q=85"
				alt="Peaceful nature morning light"
				class="w-full h-full object-cover saturate-[0.9]"
			/>
			<div class="absolute inset-0 bg-black opacity-60"></div>
		</div>

		<div class="relative z-10 max-w-7xl mx-auto px-6 w-full">
			<div class="max-w-[720px]">
				<span class="inline-block bg-white/10 border border-white/20 text-white text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-full backdrop-blur-sm mb-6">
					Mednarodna platforma za psiholosko prvo pomoc
				</span>
				<h1 class="text-white text-4xl sm:text-5xl lg:text-6xl font-semibold leading-[1.1] tracking-tight mb-5">
					Ko te boli - tu je korak za korakom.
				</h1>
				<p class="text-white/90 text-lg sm:text-xl leading-relaxed mb-8 max-w-xl">
					Brezplacni vodic: 3 napake, ki podaljsajo bolecino. Vpisi email in ga prejmi takoj.
				</p>

				<form id="lead-form-sl" class="flex flex-col sm:flex-row gap-3 max-w-lg">
					<input
						type="email"
						name="email"
						required
						placeholder="Vpisi email"
						class="flex-1 px-5 py-4 rounded text-text placeholder-gray-400 outline-none focus:ring-2 focus:ring-accent shadow-lg"
					/>
					<button
						type="submit"
						class="bg-primary text-white font-semibold px-7 py-4 rounded hover:bg-accent transition-all whitespace-nowrap disabled:opacity-70"
					>
						Prenesi brezplacni vodic ->
					</button>
				</form>
				<p id="form-msg-sl" class="hidden text-white text-sm mt-3" role="alert"></p>
				<p class="text-white/80 text-sm mt-4">Brezplacno | Anonimno | Brez kreditne kartice</p>
			</div>
		</div>
	</section>

	<!-- Social Proof Section -->
	<section class="bg-social-bg text-white py-12">
		<div class="max-w-7xl mx-auto px-6">
			<div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-white/10">
				<div class="px-6 py-6 md:py-0 text-center first:pl-0 last:pr-0">
					<strong class="block text-lg font-semibold mb-1">Psiholosko preverjeno</strong>
					<span class="text-sm opacity-75">Strokovno preverjena vsebina</span>
				</div>
				<div class="px-6 py-6 md:py-0 text-center first:pl-0 last:pr-0">
					<strong class="block text-lg font-semibold mb-1">21-dnevni program</strong>
					<span class="text-sm opacity-75">En email na dan</span>
				</div>
				<div class="px-6 py-6 md:py-0 text-center first:pl-0 last:pr-0">
					<strong class="block text-lg font-semibold mb-1">Anonimno</strong>
					<span class="text-sm opacity-75">Nihce ne ve. Samo ti.</span>
				</div>
			</div>
		</div>
	</section>

	<!-- Programs Section -->
	<section id="programs" class="py-24 bg-white">
		<div class="max-w-7xl mx-auto px-6">
			<span class="block text-xs font-bold uppercase tracking-[0.12em] text-accent mb-8">
				Programi
			</span>
			<div class="grid md:grid-cols-2 gap-6">
				<!-- Card 1 -->
				<article class="relative bg-card-active border border-card-active-border rounded-lg overflow-hidden flex flex-col justify-between">
					<div class="h-[3px] bg-primary" />
					<div class="p-10 sm:p-12 flex-1 flex flex-col justify-between">
						<div>
							<svg class="w-11 h-11 mb-5 text-primary" viewBox="0 0 48 48" fill="none" aria-hidden="true">
								<path d="M12 34C21 33 33 25 38 10C23 12 14 21 12 34Z" fill="currentColor" opacity="0.22" />
								<path d="M12 36C19 28 27 22 38 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
								<path d="M14 36C20 38 29 36 35 29" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
							</svg>
							<h3 class="text-2xl font-semibold mb-2">Po razhodu</h3>
							<p class="text-gray-600 mb-8">Ko misli nanj/o ne gredo stran.</p>
						</div>
						
						<!-- Gender Toggler Selection -->
						<div id="gender-selection-container">
							<button id="show-gender-btn" class="w-full bg-primary text-white font-semibold py-4 rounded hover:bg-accent transition-colors">
								Zacni program ->
							</button>
							
							<div id="gender-links" class="hidden flex-col sm:flex-row gap-3 animate-fade-in">
								<?php
								$stripe_sl_f = get_theme_mod( 'stripe_link_sl_f', 'https://buy.stripe.com/REPLACE_SLOVENIAN_FEMALE' );
								$stripe_sl_m = get_theme_mod( 'stripe_link_sl_m', 'https://buy.stripe.com/REPLACE_SLOVENIAN_MALE' );
								
								$is_f_ready = ( ! empty( $stripe_sl_f ) && strpos( $stripe_sl_f, 'REPLACE' ) === false );
								$is_m_ready = ( ! empty( $stripe_sl_m ) && strpos( $stripe_sl_m, 'REPLACE' ) === false );

								if ( $is_f_ready ) :
								?>
									<a href="<?php echo esc_url( $stripe_sl_f ); ?>" class="flex-1 text-center bg-primary text-white font-semibold py-4 rounded hover:bg-accent transition-colors">
										Za zenske
									</a>
								<?php else : ?>
									<button disabled class="flex-1 text-center bg-gray-300 text-gray-500 font-semibold py-4 rounded cursor-not-allowed">
										Za zenske
									</button>
								<?php endif; ?>

								<?php if ( $is_m_ready ) : ?>
									<a href="<?php echo esc_url( $stripe_sl_m ); ?>" class="flex-1 text-center bg-primary text-white font-semibold py-4 rounded hover:bg-accent transition-colors">
										Za moske
									</a>
								<?php else : ?>
									<button disabled class="flex-1 text-center bg-gray-300 text-gray-500 font-semibold py-4 rounded cursor-not-allowed">
										Za moske
									</button>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</article>

				<!-- Card 2 -->
				<article class="relative bg-[#fafafa] opacity-70 rounded-lg overflow-hidden cursor-default flex flex-col justify-between">
					<div class="h-[3px] bg-gray-300" />
					<div class="p-10 sm:p-12 flex-1 flex flex-col justify-between">
						<div>
							<svg class="w-11 h-11 mb-5 text-gray-400" viewBox="0 0 48 48" fill="none" aria-hidden="true">
								<path d="M31 8C24 11 19 18 19 26C19 34 25 40 33 41C30 44 26 46 21 46C11 46 3 38 3 28C3 18 11 10 21 10C25 10 28 11 31 8Z" fill="currentColor" opacity="0.28" />
								<path d="M34 14L35.5 17L39 17.5L36.5 20L37 23.5L34 22L31 23.5L31.5 20L29 17.5L32.5 17L34 14Z" fill="currentColor" />
							</svg>
							<h3 class="text-2xl font-semibold mb-2">Nespecnost</h3>
							<p class="text-gray-600 mb-8">Tvoja pot do mirnega spanja.</p>
						</div>
						<button disabled class="w-full bg-gray-300 text-gray-500 font-semibold py-4 rounded cursor-not-allowed">
							Kmalu
						</button>
					</div>
				</article>
			</div>
		</div>
	</section>

	<!-- How It Works Section -->
	<section class="bg-steps-bg py-24">
		<div class="max-w-7xl mx-auto px-6">
			<span class="block text-xs font-bold uppercase tracking-[0.12em] text-accent mb-10">
				Kako deluje
			</span>
			<div class="flex flex-col md:flex-row md:items-center gap-8 md:gap-0">
				<!-- Step 1 -->
				<div class="flex items-start gap-5 flex-1">
					<div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center font-bold text-lg shrink-0">
						1
					</div>
					<div class="flex-1">
						<h3 class="text-lg font-semibold mb-1">Vpisi email</h3>
						<p class="text-gray-600 text-sm leading-relaxed">Dobis brezplacni vodic takoj.</p>
					</div>
					<div class="hidden md:block w-px h-12 bg-card-active-border mx-6 shrink-0 self-center" aria-hidden="true"></div>
				</div>
				<!-- Step 2 -->
				<div class="flex items-start gap-5 flex-1">
					<div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center font-bold text-lg shrink-0">
						2
					</div>
					<div class="flex-1">
						<h3 class="text-lg font-semibold mb-1">Preberi</h3>
						<p class="text-gray-600 text-sm leading-relaxed">5 minut. 3 napake, ki jih verjetno delas.</p>
					</div>
					<div class="hidden md:block w-px h-12 bg-card-active-border mx-6 shrink-0 self-center" aria-hidden="true"></div>
				</div>
				<!-- Step 3 -->
				<div class="flex items-start gap-5 flex-1">
					<div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center font-bold text-lg shrink-0">
						3
					</div>
					<div class="flex-1">
						<h3 class="text-lg font-semibold mb-1">Zacni 21-dnevni program</h3>
						<p class="text-gray-600 text-sm leading-relaxed">EUR 39.90. En email na dan. Korak za korakom.</p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- CTA Section -->
	<section class="bg-primary text-white py-24 text-center">
		<div class="max-w-3xl mx-auto px-6">
			<h2 class="text-3xl sm:text-4xl font-semibold mb-4">21-dnevni program za okrevanje po razhodu</h2>
			<p class="text-xl sm:text-2xl mb-8 flex items-center justify-center flex-wrap gap-3">
				<span class="line-through opacity-60">EUR 110.90</span>
				<span class="text-3xl sm:text-4xl font-bold">EUR 39.90</span>
				<span class="opacity-90 text-lg">| Takojsen dostop</span>
			</p>
			<?php if ( $is_f_ready ) : ?>
				<a href="<?php echo esc_url( $stripe_sl_f ); ?>" class="inline-block bg-white text-primary font-semibold text-lg px-8 py-4 rounded-lg hover:bg-light-bg transition-all hover:-translate-y-0.5 shadow-lg">
					Zacni zdaj ->
				</a>
			<?php else : ?>
				<button disabled class="inline-block bg-white/60 text-white/80 font-semibold text-lg px-8 py-4 rounded-lg cursor-not-allowed shadow-lg">
					Zacni zdaj ->
				</button>
			<?php endif; ?>
			<p class="mt-5 text-sm opacity-80">Anonimno | Takojsen dostop | Brez tveganja</p>
		</div>
	</section>
</main>

<!-- Footer -->
<footer class="bg-light-bg border-t border-footer-border py-12">
	<div class="max-w-7xl mx-auto px-6">
		<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
			<span class="text-lg font-bold text-primary">innerfirstaid.com</span>
			<span class="text-sm text-gray-500">Copyright 2026 Inner First Aid</span>
		</div>
		<div class="text-sm mb-4 flex items-center gap-2">
			<a href="<?php echo esc_url( site_url('/privacy-policy-sl') ); ?>" class="text-primary font-medium hover:underline">Politika zasebnosti</a>
			<span class="text-gray-400">|</span>
			<a href="<?php echo esc_url( site_url('/terms-of-service-sl') ); ?>" class="text-primary font-medium hover:underline">Pogoji uporabe</a>
		</div>
		<p class="text-xs text-gray-500 max-w-xl leading-relaxed">
			Ta program je za izobrazevalne namene. Ni nadomestek za strokovno pomoc.
		</p>
	</div>
</footer>

<!-- Cookie Banner -->
<div id="wp-cookie-banner" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-2xl z-[9999] transition-transform duration-300 translate-y-full">
	<div class="max-w-7xl mx-auto px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
		<p class="text-sm text-gray-600 text-center sm:text-left">
			Uporabljamo piskotke za izboljsanje uporabniske izkusnje.
		</p>
		<div class="flex items-center gap-3 shrink-0">
			<button id="cookie-decline" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">Zavrni</button>
			<button id="cookie-accept" class="bg-primary text-white px-5 py-2 rounded text-sm font-semibold hover:bg-accent transition-colors">Sprejmi</button>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Sticky Header shadow
	var header = document.getElementById('site-header');
	window.addEventListener('scroll', function() {
		if (window.scrollY > 10) {
			header.classList.add('shadow-md');
		} else {
			header.classList.remove('shadow-md');
		}
	});

	// Mobile Menu
	var menuToggle = document.getElementById('menu-toggle');
	var mobileMenu = document.getElementById('mobile-menu');
	var hamburgerIcon = document.getElementById('menu-icon-hamburger');
	var closeIcon = document.getElementById('menu-icon-close');

	menuToggle.addEventListener('click', function() {
		var expanded = menuToggle.getAttribute('aria-expanded') === 'true';
		menuToggle.setAttribute('aria-expanded', !expanded);
		mobileMenu.classList.toggle('hidden');
		mobileMenu.classList.toggle('flex');
		hamburgerIcon.classList.toggle('hidden');
		closeIcon.classList.toggle('hidden');
	});

	// Slovenian Gender selection
	var showGenderBtn = document.getElementById('show-gender-btn');
	var genderLinks = document.getElementById('gender-links');

	if (showGenderBtn && genderLinks) {
		showGenderBtn.addEventListener('click', function() {
			showGenderBtn.classList.add('hidden');
			genderLinks.classList.remove('hidden');
			genderLinks.classList.add('flex');
		});
	}

	// Handle Lead Form Submission
	var form = document.getElementById('lead-form-sl');
	var formMsg = document.getElementById('form-msg-sl');
	if (form) {
		form.addEventListener('submit', function(e) {
			e.preventDefault();
			var email = form.email.value;
			var btn = form.querySelector('button[type="submit"]');
			
			btn.disabled = true;
			var originalText = btn.innerText;
			btn.innerText = 'Posiljanje...';

			formMsg.classList.remove('hidden', 'text-green-500', 'text-red-500');
			formMsg.classList.add('text-white');
			formMsg.innerText = '';

			fetch('<?php echo esc_url( site_url("/api/leads") ); ?>', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ email: email, lang: 'sl' })
			}).then(function(res) {
				if (res.ok) {
					btn.innerText = 'Poslano!';
					formMsg.innerText = 'Brezplacni vodic je bil poslan na vas e-naslov!';
					formMsg.classList.add('text-green-400');
					formMsg.classList.remove('hidden');
					form.reset();
				} else {
					btn.disabled = false;
					btn.innerText = 'Poskusi znova';
					formMsg.innerText = 'Nekaj je slo narobe. Preverite e-naslov in poskusite znova.';
					formMsg.classList.add('text-red-400');
					formMsg.classList.remove('hidden');
				}
			}).catch(function() {
				btn.disabled = false;
				btn.innerText = 'Poskusi znova';
				formMsg.innerText = 'Nekaj je slo narobe. Preverite e-naslov in poskusite znova.';
				formMsg.classList.add('text-red-400');
				formMsg.classList.remove('hidden');
			});
		});
	}

	// Cookie Consent Handling
	var banner = document.getElementById('wp-cookie-banner');
	var acceptBtn = document.getElementById('cookie-accept');
	var declineBtn = document.getElementById('cookie-decline');

	if (!localStorage.getItem('inner_cookie_consent')) {
		setTimeout(function() {
			banner.classList.remove('translate-y-full');
		}, 1000);
	}

	acceptBtn.addEventListener('click', function() {
		localStorage.setItem('inner_cookie_consent', 'accepted');
		banner.classList.add('translate-y-full');
	});

	declineBtn.addEventListener('click', function() {
		localStorage.setItem('inner_cookie_consent', 'declined');
		banner.classList.add('translate-y-full');
	});
});
</script>

<?php
get_footer();
