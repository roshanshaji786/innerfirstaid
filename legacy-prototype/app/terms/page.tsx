import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { en } from '@/lib/content';

export const metadata = {
  title: 'Terms of Service - Inner First Aid',
  description: 'Terms of service for Inner First Aid.',
};

export default function TermsPage() {
  return (
    <>
      <Header cta={en.header.cta} lang={en.lang} />
      <main className="bg-white pt-[72px]">
        <section className="max-w-3xl mx-auto px-6 py-20">
          <h1 className="text-4xl font-semibold text-primary mb-6">Terms of Service</h1>
          <div className="space-y-5 text-gray-700 leading-relaxed">
            <p>Inner First Aid provides educational self-support content. It is not medical care, therapy, crisis intervention, or a substitute for professional support.</p>
            <p>If you are in immediate danger or may hurt yourself or someone else, contact local emergency services now.</p>
            <p>Program access and payment terms are shown at checkout through Stripe. Do not share paid program materials publicly or resell them.</p>
            <p>By using this site, you agree to use the content responsibly and understand that results vary from person to person.</p>
          </div>
        </section>
      </main>
      <Footer content={en.footer} />
    </>
  );
}
