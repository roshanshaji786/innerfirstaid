import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { en } from '@/lib/content';

export const metadata = {
  title: 'Privacy Policy - Inner First Aid',
  description: 'Privacy information for Inner First Aid.',
};

export default function PrivacyPage() {
  return (
    <>
      <Header cta={en.header.cta} lang={en.lang} />
      <main className="bg-white pt-[72px]">
        <section className="max-w-3xl mx-auto px-6 py-20">
          <h1 className="text-4xl font-semibold text-primary mb-6">Privacy Policy</h1>
          <div className="space-y-5 text-gray-700 leading-relaxed">
            <p>Inner First Aid collects the email address you submit so we can send the free guide, program emails, and related updates you request.</p>
            <p>We use cookies and analytics only after consent. You can decline cookies in the banner.</p>
            <p>We do not sell your personal information. Payment processing is handled by Stripe when you choose to buy a program.</p>
            <p>To request deletion or correction of your email record, contact the Inner First Aid team through the email address used for your purchase or guide delivery.</p>
          </div>
        </section>
      </main>
      <Footer content={en.footer} />
    </>
  );
}
