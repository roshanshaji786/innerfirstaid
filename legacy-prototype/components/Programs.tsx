'use client';

import { useState } from 'react';
import type { Content } from '@/lib/content';

export default function Programs({ content, lang }: { content: Content['programs']; lang: 'en' | 'sl' }) {
  const [showGender, setShowGender] = useState(false);

  const stripeEn = process.env.NEXT_PUBLIC_STRIPE_EN;
  const stripeSlF = process.env.NEXT_PUBLIC_STRIPE_SL_F;
  const stripeSlM = process.env.NEXT_PUBLIC_STRIPE_SL_M;
  const isReady = (url?: string) => !!url && /^https:\/\/buy\.stripe\.com\//.test(url) && !url.includes('REPLACE') && url !== 'placeholder';
  const linkClass = 'flex-1 text-center bg-primary text-white font-semibold py-4 rounded hover:bg-accent transition-colors';
  const disabledClass = 'flex-1 text-center bg-gray-300 text-gray-500 font-semibold py-4 rounded cursor-not-allowed';

  return (
    <section id="programs" className="py-24 bg-white">
      <div className="max-w-7xl mx-auto px-6">
        <span className="block text-xs font-bold uppercase tracking-[0.12em] text-accent mb-8">
          {content.label}
        </span>
        <div className="grid md:grid-cols-2 gap-6">
          <article className="relative bg-card-active border border-card-active-border rounded-lg overflow-hidden">
            <div className="h-[3px] bg-primary" />
            <div className="p-10 sm:p-12">
              <svg className="w-11 h-11 mb-5 text-primary" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                <path d="M12 34C21 33 33 25 38 10C23 12 14 21 12 34Z" fill="currentColor" opacity="0.22" />
                <path d="M12 36C19 28 27 22 38 10" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
                <path d="M14 36C20 38 29 36 35 29" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
              </svg>
              <h3 className="text-2xl font-semibold mb-2">{content.card1.title}</h3>
              <p className="text-gray-600 mb-8">{content.card1.text}</p>

              {lang === 'sl' ? (
                <div>
                  {!showGender ? (
                    <button
                      onClick={() => setShowGender(true)}
                      className="w-full bg-primary text-white font-semibold py-4 rounded hover:bg-accent transition-colors"
                    >
                      {content.card1.btn}
                    </button>
                  ) : (
                    <div className="flex flex-col sm:flex-row gap-3 animate-[fadeIn_0.3s_ease]">
                      {isReady(stripeSlF) ? (
                        <a href={stripeSlF} className={linkClass}>
                          {content.card1.btnFemale}
                        </a>
                      ) : (
                        <button disabled className={disabledClass}>
                          {content.card1.btnFemale}
                        </button>
                      )}
                      {isReady(stripeSlM) ? (
                        <a href={stripeSlM} className={linkClass}>
                          {content.card1.btnMale}
                        </a>
                      ) : (
                        <button disabled className={disabledClass}>
                          {content.card1.btnMale}
                        </button>
                      )}
                    </div>
                  )}
                </div>
              ) : (
                isReady(stripeEn) ? (
                  <a href={stripeEn} className="block w-full text-center bg-primary text-white font-semibold py-4 rounded hover:bg-accent transition-colors">
                    {content.card1.btn}
                  </a>
                ) : (
                  <button disabled className="block w-full text-center bg-gray-300 text-gray-500 font-semibold py-4 rounded cursor-not-allowed">
                    {content.card1.btn}
                  </button>
                )
              )}
            </div>
          </article>

          <article className="relative bg-[#fafafa] opacity-70 rounded-lg overflow-hidden cursor-default">
            <div className="h-[3px] bg-gray-300" />
            <div className="p-10 sm:p-12">
              <svg className="w-11 h-11 mb-5 text-gray-400" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                <path d="M31 8C24 11 19 18 19 26C19 34 25 40 33 41C30 44 26 46 21 46C11 46 3 38 3 28C3 18 11 10 21 10C25 10 28 11 31 8Z" fill="currentColor" opacity="0.28" />
                <path d="M34 14L35.5 17L39 17.5L36.5 20L37 23.5L34 22L31 23.5L31.5 20L29 17.5L32.5 17L34 14Z" fill="currentColor" />
              </svg>
              <h3 className="text-2xl font-semibold mb-2">{content.card2.title}</h3>
              <p className="text-gray-600 mb-8">{content.card2.text}</p>
              <button disabled className="w-full bg-gray-300 text-gray-500 font-semibold py-4 rounded cursor-not-allowed">
                {content.card2.btn}
              </button>
            </div>
          </article>
        </div>
      </div>
    </section>
  );
}
