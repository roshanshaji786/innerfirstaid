import Header from '@/components/Header';
import Hero from '@/components/Hero';
import SocialProof from '@/components/SocialProof';
import Programs from '@/components/Programs';
import HowItWorks from '@/components/HowItWorks';
import CTASection from '@/components/CTASection';
import Footer from '@/components/Footer';
import CookieBanner from '@/components/CookieBanner';
import Analytics from '@/components/Analytics';
import { sl } from '@/lib/content';

export const metadata = {
  title: sl.metaTitle,
  description: sl.metaDesc,
  alternates: { canonical: '/sl' },
};

export default function SloPage() {
  return (
    <div lang="sl">
      <Header cta={sl.header.cta} lang={sl.lang} />
      <main>
        <Hero content={sl.hero} lang={sl.lang} />
        <SocialProof items={sl.social} />
        <Programs content={sl.programs} lang={sl.lang} />
        <HowItWorks content={sl.steps} />
        <CTASection content={sl.cta} stripeEnvKey="NEXT_PUBLIC_STRIPE_SL_F" />
      </main>
      <Footer content={sl.footer} />
      <CookieBanner text={sl.cookie.text} accept={sl.cookie.accept} decline={sl.cookie.decline} />
      <Analytics />
    </div>
  );
}
