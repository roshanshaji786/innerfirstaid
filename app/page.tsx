import Header from '@/components/Header';
import Hero from '@/components/Hero';
import SocialProof from '@/components/SocialProof';
import Programs from '@/components/Programs';
import HowItWorks from '@/components/HowItWorks';
import CTASection from '@/components/CTASection';
import Footer from '@/components/Footer';
import CookieBanner from '@/components/CookieBanner';
import Analytics from '@/components/Analytics';
import { en } from '@/lib/content';

export default function Home() {
  return (
    <>
      <Header cta={en.header.cta} lang={en.lang} />
      <main>
        <Hero content={en.hero} lang={en.lang} />
        <SocialProof items={en.social} />
        <Programs content={en.programs} lang={en.lang} />
        <HowItWorks content={en.steps} />
        <CTASection content={en.cta} stripeEnvKey="NEXT_PUBLIC_STRIPE_EN" />
      </main>
      <Footer content={en.footer} />
      <CookieBanner text={en.cookie.text} accept={en.cookie.accept} decline={en.cookie.decline} />
      <Analytics />
    </>
  );
}
