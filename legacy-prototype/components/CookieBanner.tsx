'use client';

import { useEffect, useState } from 'react';

export default function CookieBanner({ text, accept, decline }: { text: string; accept: string; decline: string }) {
  const [show, setShow] = useState(false);

  useEffect(() => {
    if (!localStorage.getItem('ifa_cookie_consent')) {
      const t = setTimeout(() => setShow(true), 1200);
      return () => clearTimeout(t);
    }
  }, []);

  const handleAccept = () => {
    localStorage.setItem('ifa_cookie_consent', 'granted');
    setShow(false);
    if (typeof window !== 'undefined') {
      window.dispatchEvent(new Event('cookieConsent'));
    }
  };

  const handleDecline = () => {
    localStorage.setItem('ifa_cookie_consent', 'denied');
    setShow(false);
  };

  if (!show) return null;

  return (
    <div className="fixed bottom-0 left-0 right-0 z-[9999] bg-white border-t border-gray-200 shadow-[0_-4px_24px_rgba(0,0,0,0.08)] transition-transform duration-500">
      <div className="max-w-7xl mx-auto px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p className="text-sm text-gray-600">{text}</p>
        <div className="flex gap-3 shrink-0">
          <button onClick={handleDecline} className="text-sm text-gray-500 hover:text-text px-3 py-2 transition-colors">{decline}</button>
          <button onClick={handleAccept} className="text-sm font-semibold bg-primary text-white px-5 py-2 rounded hover:bg-accent transition-colors">{accept}</button>
        </div>
      </div>
    </div>
  );
}
