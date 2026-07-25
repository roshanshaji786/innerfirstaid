'use client';

import { useState } from 'react';
import type { Content } from '@/lib/content';

export default function Programs({ content, lang }: { content: Content['programs']; lang: 'en' | 'sl' }) {
  const [showGender, setShowGender] = useState(false);

  const stripeEn = process.env.NEXT_PUBLIC_STRIPE_EN;
  const stripeSlF = process.env.NEXT_PUBLIC_STRIPE_SL_F;
  const stripeSlM = process.env.NEXT_PUBLIC_STRIPE_SL_M;

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
              <div className="text-4xl mb-5" aria-hidden="true">🌿</div>
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
                      <a href={stripeSlF} className="flex-1 text-center bg-primary text-white font-semibold py-4 rounded hover:bg-accent transition-colors">
                        {content.card1.btnFemale}
                      </a>
                      <a href={stripeSlM} className="flex-1 text-center bg-primary text-white font-semibold py-4 rounded hover:bg-accent transition-colors">
                        {content.card1.btnMale}
                      </a>
                    </div>
                  )}
                </div>
              ) : (
                <a href={stripeEn} className="block w-full text-center bg-primary text-white font-semibold py-4 rounded hover:bg-accent transition-colors">
                  {content.card1.btn}
                </a>
              )}
            </div>
          </article>

          <article className="relative bg-[#fafafa] opacity-70 rounded-lg overflow-hidden cursor-default">
            <div className="h-[3px] bg-gray-300" />
            <div className="p-10 sm:p-12">
              <div className="text-4xl mb-5" aria-hidden="true">🌙</div>
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
