'use client';

import Script from 'next/script';
import { useEffect, useState } from 'react';

export default function Analytics() {
  const [consent, setConsent] = useState(false);

  useEffect(() => {
    if (typeof window === 'undefined') return;
    if (localStorage.getItem('ifa_cookie_consent') === 'granted') {
      setConsent(true);
    }
    const handler = () => setConsent(true);
    window.addEventListener('cookieConsent', handler);
    return () => window.removeEventListener('cookieConsent', handler);
  }, []);

  if (!consent) return null;

  const GA_ID = process.env.NEXT_PUBLIC_GA_ID;
  const PIXEL_ID = process.env.NEXT_PUBLIC_META_PIXEL_ID;
  const hasGa = !!GA_ID && GA_ID !== 'placeholder' && GA_ID !== 'G-XXXXXXXXXX';
  const hasPixel = !!PIXEL_ID && PIXEL_ID !== 'placeholder' && PIXEL_ID !== 'XXXXXXXXXXXXXXXX';

  return (
    <>
      {hasGa && (
        <>
          <Script src={`https://www.googletagmanager.com/gtag/js?id=${GA_ID}`} strategy="afterInteractive" />
          <Script id="ga" strategy="afterInteractive">
            {`
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', '${GA_ID}', { anonymize_ip: true });
            `}
          </Script>
        </>
      )}
      {hasPixel && (
        <Script id="meta-pixel" strategy="afterInteractive">
          {`
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '${PIXEL_ID}');
            fbq('track', 'PageView');
          `}
        </Script>
      )}
    </>
  );
}
