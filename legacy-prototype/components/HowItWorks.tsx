import type { Content } from '@/lib/content';

export default function HowItWorks({ content }: { content: Content['steps'] }) {
  return (
    <section className="bg-steps-bg py-24">
      <div className="max-w-7xl mx-auto px-6">
        <span className="block text-xs font-bold uppercase tracking-[0.12em] text-accent mb-10">
          {content.label}
        </span>
        <div className="flex flex-col md:flex-row md:items-center gap-8 md:gap-0">
          {content.items.map((step, i) => (
            <div key={step.num} className="flex items-start gap-5 flex-1">
              <div className="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center font-bold text-lg shrink-0">
                {step.num}
              </div>
              <div className="flex-1">
                <h3 className="text-lg font-semibold mb-1">{step.title}</h3>
                <p className="text-gray-600 text-sm leading-relaxed">{step.text}</p>
              </div>
              {i < content.items.length - 1 && (
                <div className="hidden md:block w-px h-12 bg-card-active-border mx-6 shrink-0 self-center" aria-hidden="true" />
              )}
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
