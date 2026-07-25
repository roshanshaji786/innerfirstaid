'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';

export default function Header({ cta, lang }: { cta: string; lang: string }) {
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 10);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  const homePath = lang === 'sl' ? '/sl' : '/';

  return (
    <header
      className={`fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 transition-shadow duration-300 ${
        scrolled ? 'shadow-md' : ''
      }`}
    >
      <div className="max-w-7xl mx-auto px-6 h-[72px] flex items-center justify-between">
        <Link href={homePath} className="flex items-center gap-3 text-primary" aria-label="Inner First Aid Home">
          <svg width="32" height="32" viewBox="0 0 32 32" fill="none" className="shrink-0" aria-hidden="true">
            <circle cx="16" cy="16" r="15" stroke="currentColor" strokeWidth="2" />
            <path d="M10 16C10 12 13 9 16 9C19 9 22 12 22 16C22 20 19 23 16 23C13 23 10 20 10 16Z" fill="currentColor" opacity="0.2" />
            <path d="M16 12V20M12 16H20" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
          </svg>
          <span className="text-lg font-bold tracking-tight">innerfirstaid.com</span>
        </Link>

        <button
          className="lg:hidden p-2 text-text"
          onClick={() => setMenuOpen(!menuOpen)}
          aria-label="Toggle menu"
          aria-expanded={menuOpen}
        >
          {menuOpen ? (
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M6 6L18 18M18 6L6 18" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            </svg>
          ) : (
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M4 7H20M4 12H20M4 17H20" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            </svg>
          )}
        </button>

        <nav
          className={`${
            menuOpen ? 'flex' : 'hidden'
          } lg:flex absolute lg:relative top-[72px] lg:top-auto left-0 right-0 bg-white lg:bg-transparent flex-col lg:flex-row items-center justify-center lg:justify-end gap-6 p-8 lg:p-0 border-b lg:border-b-0 border-gray-200`}
        >
          <a
            href="#programs"
            className="bg-primary text-white font-semibold px-5 py-2.5 rounded hover:bg-accent transition-colors"
            onClick={() => setMenuOpen(false)}
          >
            {cta}
          </a>
        </nav>
      </div>
    </header>
  );
}
