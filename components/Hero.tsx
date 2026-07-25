'use client';

import Image from 'next/image';
import { useState } from 'react';
import type { Content } from '@/lib/content';

export default function Hero({ content, lang }: { content: Content['hero']; lang: string }) {
  const [email, setEmail] = useState('');
  const [status, setStatus] = useState<'idle' | 'loading' | 'success'>('idle');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email) return;
    setStatus('loading');
    try {
      const res = await fetch('/api/leads', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, lang }),
      });
      if (res.ok) setStatus('success');
      else setStatus('idle');
    } catch {
      setStatus('idle');
    }
  };

  return (
    <section className="relative min-h-[100dvh] flex items-center pt-[72px] overflow-hidden">
      <div className="absolute inset-0">
        <Image
          src="https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=2400&q=85"
          alt="Peaceful nature morning light"
          fill
          className="object-cover saturate-[0.9]"
          priority
          sizes="100vw"
          unoptimized
        />
        <div className="absolute inset-0 bg-[rgba(10,35,18,0.62)]" />
      </div>

      <div className="relative z-10 max-w-7xl mx-auto px-6 py-20 w-full">
        <div className="max-w-[720px]">
          <span className="inline-block bg-white/10 border border-white/20 text-white text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-full backdrop-blur-sm mb-6">
            {content.badge}
          </span>
          <h1 className="text-white text-4xl sm:text-5xl lg:text-6xl font-semibold leading-[1.1] tracking-tight mb-5">
            {content.headline}
          </h1>
          <p className="text-white/90 text-lg sm:text-xl leading-relaxed mb-8 max-w-xl">
            {content.subheadline}
          </p>

          <form onSubmit={handleSubmit} className="flex flex-col sm:flex-row gap-3 max-w-lg">
            <label htmlFor={`email-${lang}`} className="sr-only">
              {content.placeholder}
            </label>
            <input
              id={`email-${lang}`}
              type="email"
              required
              placeholder={content.placeholder}
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="flex-1 px-5 py-4 rounded text-text placeholder-gray-400 outline-none focus:ring-2 focus:ring-accent shadow-lg"
            />
            <button
              type="submit"
              disabled={status === 'loading' || status === 'success'}
              className="bg-primary text-white font-semibold px-7 py-4 rounded hover:bg-accent transition-all whitespace-nowrap disabled:opacity-70"
            >
              {status === 'success' ? 'Sent!' : status === 'loading' ? '...' : content.formBtn}
            </button>
          </form>
          <p className="text-white/80 text-sm mt-4">{content.formNote}</p>
        </div>
      </div>
    </section>
  );
}
