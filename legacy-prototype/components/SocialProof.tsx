import type { Content } from '@/lib/content';

export default function SocialProof({ items }: { items: Content['social'] }) {
  return (
    <section className="bg-social-bg text-white py-12">
      <div className="max-w-7xl mx-auto px-6">
        <div className="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-white/10">
          {items.map((item, i) => (
            <div key={i} className="px-6 py-6 md:py-0 text-center first:pl-0 last:pr-0">
              <strong className="block text-lg font-semibold mb-1">{item.title}</strong>
              <span className="text-sm opacity-75">{item.subtitle}</span>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
