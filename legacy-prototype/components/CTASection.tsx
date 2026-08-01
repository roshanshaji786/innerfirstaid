import type { Content } from '@/lib/content';

export default function CTASection({ content, stripeEnvKey }: { content: Content['cta']; stripeEnvKey: string }) {
  const link = process.env[stripeEnvKey];
  const isReady = !!link && /^https:\/\/buy\.stripe\.com\//.test(link) && !link.includes('REPLACE') && link !== 'placeholder';

  return (
    <section className="bg-primary text-white py-24 text-center">
      <div className="max-w-3xl mx-auto px-6">
        <h2 className="text-3xl sm:text-4xl font-semibold mb-4">{content.title}</h2>
        <p className="text-xl sm:text-2xl mb-8 flex items-center justify-center flex-wrap gap-3">
          <span className="line-through opacity-60">{content.priceOld}</span>
          <span className="text-3xl sm:text-4xl font-bold">{content.priceNew}</span>
          <span className="opacity-90 text-lg">{content.priceNote}</span>
        </p>
        {isReady ? (
          <a href={link} className="inline-block bg-white text-primary font-semibold text-lg px-8 py-4 rounded-lg hover:bg-light-bg transition-all hover:-translate-y-0.5 shadow-lg">
            {content.btn}
          </a>
        ) : (
          <button disabled className="inline-block bg-white/60 text-white/80 font-semibold text-lg px-8 py-4 rounded-lg cursor-not-allowed shadow-lg">
            {content.btn}
          </button>
        )}
        <p className="mt-5 text-sm opacity-80">{content.note}</p>
      </div>
    </section>
  );
}
