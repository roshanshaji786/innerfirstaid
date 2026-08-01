import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import './globals.css';

const inter = Inter({
  subsets: ['latin'],
  variable: '--font-inter',
  display: 'swap',
});

export const metadata: Metadata = {
  metadataBase: new URL('https://innerfirstaid.com'),
  title: 'Inner First Aid - Free Guide & 21-Day Program',
  description: 'The international platform for psychological first aid.',
  openGraph: {
    title: 'Inner First Aid',
    description: 'When it hurts - here is step by step.',
    type: 'website',
    url: '/',
  },
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className={inter.variable}>
      <body className="font-sans text-text antialiased">{children}</body>
    </html>
  );
}
