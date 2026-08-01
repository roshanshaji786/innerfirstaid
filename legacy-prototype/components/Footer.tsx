import type { Content } from '@/lib/content';

export default function Footer({ content }: { content: Content['footer'] }) {
  return (
    <footer className="bg-light-bg border-t border-footer-border py-12">
      <div className="max-w-7xl mx-auto px-6">
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
          <span className="text-lg font-bold text-primary">innerfirstaid.com</span>
          <span className="text-sm text-gray-500">{content.copy}</span>
        </div>
        <div className="text-sm mb-4">
          <a href="/privacy" className="text-primary font-medium hover:underline mr-2">{content.privacy}</a>
          <span className="text-gray-400">|</span>
          <a href="/terms" className="text-primary font-medium hover:underline ml-2">{content.terms}</a>
        </div>
        <p className="text-xs text-gray-500 max-w-xl leading-relaxed">{content.disclaimer}</p>
      </div>
    </footer>
  );
}
