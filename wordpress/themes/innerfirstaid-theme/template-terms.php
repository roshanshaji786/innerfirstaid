<?php
/**
 * Template Name: Terms of Service Page
 *
 * This is the template for displaying the Terms of Service.
 *
 * @package InnerFirstAid
 */

get_header();
?>

<!-- Header -->
<header class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200">
	<div class="max-w-7xl mx-auto px-6 h-[72px] flex items-center justify-between">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-3 text-primary" aria-label="Inner First Aid Home">
			<svg width="32" height="32" viewBox="0 0 32 32" fill="none" class="shrink-0" aria-hidden="true">
				<circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="2" />
				<path d="M10 16C10 12 13 9 16 9C19 9 22 12 22 16C22 20 19 23 16 23C13 23 10 20 10 16Z" fill="currentColor" opacity="0.2" />
				<path d="M16 12V20M12 16H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
			</svg>
			<span class="text-lg font-bold tracking-tight">innerfirstaid.com</span>
		</a>
	</div>
</header>

<main class="bg-white pt-[72px]">
	<section class="max-w-3xl mx-auto px-6 py-20">
		<h1 class="text-4xl font-semibold text-primary mb-6">Terms of Service</h1>
		<div class="space-y-5 text-gray-700 leading-relaxed">
			<p>Inner First Aid provides educational self-support content. It is not medical care, therapy, crisis intervention, or a substitute for professional support.</p>
			<p>If you are in immediate danger or may hurt yourself or someone else, contact local emergency services now.</p>
			<p>Program access and payment terms are shown at checkout through Stripe. Do not share paid program materials publicly or resell them.</p>
			<p>By using this site, you agree to use the content responsibly and understand that results vary from person to person.</p>
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
		<p class="text-xs text-gray-500 max-w-xl leading-relaxed">
			This program is for educational purposes only. It is not a substitute for professional support.
		</p>
	</div>
</footer>

<?php
get_footer();
