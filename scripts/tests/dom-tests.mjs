#!/usr/bin/env node
/**
 * DOM interaction tests for the Inner First Aid WordPress build.
 *
 * Runs real user-level interactions (clicks, form submits, localStorage)
 * against the live site using jsdom. Requires:
 *   - the WP test site running on the URL below (see run-tests.sh)
 *   - `npm install` in this directory (jsdom)
 *
 * Usage: node dom-tests.mjs [baseUrl]
 */

import { JSDOM, VirtualConsole } from 'jsdom';

const BASE = process.argv[2] || 'http://127.0.0.1:8899';

let passed = 0;
let failed = 0;
const failures = [];

function check(name, cond, extra) {
  if (cond) {
    passed++;
    console.log(`  PASS  ${name}`);
  } else {
    failed++;
    failures.push(name + (extra ? ` — ${extra}` : ''));
    console.log(`  FAIL  ${name}${extra ? ` — ${extra}` : ''}`);
  }
}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function loadPage(path) {
  const url = BASE + path;

  // jsdom does not fetch the document itself — fetch it, then feed the HTML.
  const res = await fetch(url);
  const html = await res.text();

  const vc = new VirtualConsole();
  vc.on('jsdomError', () => {}); // ignore resource errors (fonts, images)

  const dom = await new Promise((resolve, reject) => {
    const d = new JSDOM(html, {
      url,
      runScripts: 'dangerously',
      resources: 'usable',
      pretendToBeVisual: true,
      virtualConsole: vc,
      beforeParse(window) {
        // Node fetch bridge (jsdom has no fetch).
        window.fetch = (...args) => globalThis.fetch(...args);

        // Polyfills for APIs browsers have but jsdom lacks (Elementor JS needs them).
        if (!window.IntersectionObserver) {
          window.IntersectionObserver = class {
            constructor(cb) { this.cb = cb; }
            observe() {}
            unobserve() {}
            disconnect() {}
          };
        }
        if (!window.ResizeObserver) {
          window.ResizeObserver = class {
            observe() {}
            unobserve() {}
            disconnect() {}
          };
        }
        if (!window.matchMedia) {
          window.matchMedia = () => ({ matches: false, addListener() {}, removeListener() {}, addEventListener() {}, removeEventListener() {} });
        }
      },
    });
    d.window.addEventListener('load', () => resolve(d));
    setTimeout(() => reject(new Error(`load timeout for ${path}`)), 30000);
  });

  await sleep(100);
  return dom;
}

const summary = () => {
  console.log(`\nDOM tests: ${passed} passed, ${failed} failed`);
  if (failures.length) {
    console.log('Failures:\n - ' + failures.join('\n - '));
  }
  process.exit(failed ? 1 : 0);
};

// ---------------------------------------------------------------- EN home
console.log('\n[EN home /]');

const home = await loadPage('/');
const hDoc = home.window.document;

// 1. Header & mobile menu
check('header present', !!hDoc.querySelector('[data-ifa-header]'));
check('logo links home', (hDoc.querySelector('.ifa-logo')?.getAttribute('href') || '') === BASE + '/');
check('header CTA points to #programs', (hDoc.querySelector('[data-ifa-cta]')?.getAttribute('href') || '') === '#programs');

const navToggle = hDoc.querySelector('[data-ifa-nav-toggle]');
const nav = hDoc.querySelector('[data-ifa-nav]');
if (navToggle && nav) {
  navToggle.click();
  check('mobile menu opens on click', nav.classList.contains('is-open') && navToggle.getAttribute('aria-expanded') === 'true');
  navToggle.click();
  check('mobile menu closes on second click', !nav.classList.contains('is-open'));
} else {
  check('mobile menu toggle exists', false);
}

// 2. Hero + lead form validation (no network call for invalid email)
const form = hDoc.querySelector('[data-ifa-lead-form]');
check('lead form present', !!form);
if (form) {
  const input = form.querySelector('input[name="email"]');
  const status = form.querySelector('[data-ifa-lead-status]');
  input.value = 'not-an-email';
  form.dispatchEvent(new home.window.Event('submit', { bubbles: true, cancelable: true }));
  await sleep(300);
  check('invalid email shows error message', (status.textContent || '').length > 0 && status.classList.contains('is-error'));
}

// 3. Cookie banner
const banner = hDoc.querySelector('[data-ifa-cookie]');
check('cookie banner markup present', !!banner);
if (banner) {
  check('banner hidden until delay', banner.hidden === true);
  await sleep(1400);
  check('banner shows after 1.2s', banner.hidden === false);
  const accept = hDoc.querySelector('[data-ifa-cookie-accept-btn]');
  check('accept button labelled', (accept?.textContent || '') === 'Accept');
  accept.click();
  await sleep(600);
  check('banner hides after accept', banner.hidden === true);
  check('consent stored as granted', home.window.localStorage.getItem('ifa_cookie_consent') === 'granted');
}

// 4. Real AJAX submit (valid email -> stored)
const form2 = hDoc.querySelector('[data-ifa-lead-form]');
const input2 = form2.querySelector('input[name="email"]');
const status2 = form2.querySelector('[data-ifa-lead-status]');
input2.value = 'domtest@example.com';
form2.dispatchEvent(new home.window.Event('submit', { bubbles: true, cancelable: true }));
await sleep(2500);
check('valid email shows success message', (status2.textContent || '').includes('Sent'));
check('input cleared after success', input2.value === '');

// 5. Social proof + steps + pricing
check('social proof renders 3 items', hDoc.querySelectorAll('.ifa-social__item').length === 3);
check('steps render 3 items', hDoc.querySelectorAll('.ifa-steps__item').length === 3);
check('pricing shows new price', (hDoc.querySelector('.ifa-pricing__new')?.textContent || '').includes('39.90'));

// 6. Stripe CTAs are live links after configuration
const stripeLinks = [...hDoc.querySelectorAll('a[href^="https://buy.stripe.com/"]')];
check('stripe links present (programs + pricing)', stripeLinks.length >= 2);

home.window.close();

// ---------------------------------------------------------------- SL home
console.log('\n[SL home /sl/]');

const sl = await loadPage('/sl/');
const sDoc = sl.window.document;

check('html lang is sl-SI', sDoc.documentElement.getAttribute('lang') === 'sl-SI');
check('SL headline renders', (sDoc.querySelector('.ifa-hero__title')?.textContent || '').includes('Ko te boli'));
check('SL header CTA', (sDoc.querySelector('[data-ifa-cta]')?.textContent || '') === 'Zacni brezplacno');

// 7. Gender picker flow
const genderToggle = sDoc.querySelector('[data-ifa-gender-toggle]');
check('gender toggle button present', !!genderToggle);
if (genderToggle) {
  const options = sDoc.querySelector('[data-ifa-gender-options]');
  check('gender options hidden initially', options.hidden === true);
  genderToggle.click();
  await sleep(200);
  check('gender options reveal on click', options.hidden === false);
  const female = sDoc.querySelector('[data-ifa-gender-options] a[href^="https://buy.stripe.com/SLF"]');
  const male = sDoc.querySelector('[data-ifa-gender-options] a[href^="https://buy.stripe.com/SLM"]');
  check('female link -> SLF stripe url', !!female);
  check('male link -> SLM stripe url', !!male);
}

// 8. Language switch in header
const langEn = sDoc.querySelector('[data-ifa-lang="en"]');
check('SL page links to EN', (langEn?.getAttribute('href') || '') === BASE + '/');

// 9. Cookie banner uses SL texts
const slBanner = sDoc.querySelector('[data-ifa-cookie]');
check('SL cookie banner present', !!slBanner);
if (slBanner) {
  await sleep(1400);
  check('SL accept label', (sDoc.querySelector('[data-ifa-cookie-accept-btn]')?.textContent || '') === 'Sprejmi');
  sDoc.querySelector('[data-ifa-cookie-decline-btn]').click();
  await sleep(600);
  check('decline stores denied', sl.window.localStorage.getItem('ifa_cookie_consent') === 'denied');
}

// 10. SL lead form submits with lang=sl
const slForm = sDoc.querySelector('[data-ifa-lead-form]');
const slInput = slForm.querySelector('input[name="email"]');
slInput.value = 'sl-test@example.com';
slForm.dispatchEvent(new sl.window.Event('submit', { bubbles: true, cancelable: true }));
await sleep(2500);
check('SL form success message', (slForm.querySelector('[data-ifa-lead-status]')?.textContent || '').includes('Poslano'));

sl.window.close();

// ---------------------------------------------------------------- Legal pages
console.log('\n[Legal pages]');

const priv = await loadPage('/privacy/');
check('privacy page renders heading', (priv.window.document.querySelector('h1')?.textContent || '').includes('Privacy Policy'));
priv.window.close();

const terms = await loadPage('/terms/');
check('terms page renders heading', (terms.window.document.querySelector('h1')?.textContent || '').includes('Terms of Service'));
terms.window.close();

const slPriv = await loadPage('/sl/privacy/');
check('SL privacy renders', (slPriv.window.document.querySelector('h1')?.textContent || '').includes('Politika zasebnosti'));
slPriv.window.close();

// ---------------------------------------------------------------- 404
console.log('\n[404]');

const notFound = await loadPage('/definitely-not-a-page/');
check('404 page renders', notFound.window.document.body.textContent.includes('not found') || notFound.window.document.body.textContent.includes('ni bilo'));
notFound.window.close();

summary();
