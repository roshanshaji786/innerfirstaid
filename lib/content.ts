export interface Content {
  lang: 'en' | 'sl';
  metaTitle: string;
  metaDesc: string;
  header: { cta: string };
  hero: {
    badge: string;
    headline: string;
    subheadline: string;
    formBtn: string;
    formNote: string;
    placeholder: string;
  };
  social: { title: string; subtitle: string }[];
  programs: {
    label: string;
    card1: {
      title: string;
      text: string;
      btn: string;
      btnFemale?: string;
      btnMale?: string;
    };
    card2: { title: string; text: string; btn: string };
  };
  steps: {
    label: string;
    items: { num: string; title: string; text: string }[];
  };
  cta: {
    title: string;
    priceOld: string;
    priceNew: string;
    priceNote: string;
    btn: string;
    note: string;
  };
  footer: {
    copy: string;
    privacy: string;
    terms: string;
    disclaimer: string;
  };
  cookie: { text: string; accept: string; decline: string };
}

export const en: Content = {
  lang: 'en',
  metaTitle: 'Inner First Aid — Free Guide & 21-Day Program',
  metaDesc: 'The international platform for psychological first aid. Free guide: 3 mistakes that prolong the pain.',
  header: { cta: 'Get started free' },
  hero: {
    badge: 'The international platform for psychological first aid',
    headline: 'When it hurts — here is step by step.',
    subheadline: 'Free guide: 3 mistakes that prolong the pain. Enter your email and receive it now.',
    formBtn: 'Get free guide →',
    formNote: 'Free · Anonymous · No credit card',
    placeholder: 'Enter your email',
  },
  social: [
    { title: 'Psychologist verified', subtitle: 'Evidence-based content' },
    { title: '21-day program', subtitle: 'One email per day' },
    { title: 'Anonymous', subtitle: 'Nobody knows. Only you.' },
  ],
  programs: {
    label: 'PROGRAMS',
    card1: { title: 'After a breakup', text: "When thoughts of them won't go away.", btn: 'Start program →' },
    card2: { title: 'Insomnia', text: 'Your path to restful sleep.', btn: 'Coming soon →' },
  },
  steps: {
    label: 'HOW IT WORKS',
    items: [
      { num: '1', title: 'Enter your email', text: 'Get the free guide instantly.' },
      { num: '2', title: 'Read it', text: "5 minutes. 3 mistakes you're probably making." },
      { num: '3', title: 'Start the 21-day program', text: '€39.90. One email per day. Step by step.' },
    ],
  },
  cta: {
    title: '21-day breakup recovery program',
    priceOld: '€110.90',
    priceNew: '€39.90',
    priceNote: '· Instant access',
    btn: 'Start now →',
    note: 'Anonymous · Instant access · No risk',
  },
  footer: {
    copy: '© 2026 Inner First Aid',
    privacy: 'Privacy Policy',
    terms: 'Terms of Service',
    disclaimer: 'This program is for educational purposes only. It is not a substitute for professional support.',
  },
  cookie: {
    text: 'We use cookies to improve your experience and analyze site traffic.',
    accept: 'Accept',
    decline: 'Decline',
  },
};

export const sl: Content = {
  lang: 'sl',
  metaTitle: 'Inner First Aid — Brezplačen vodič in 21-dnevni program',
  metaDesc: 'Mednarodna platforma za psihološko prvo pomoč. Brezplačen vodič: 3 napake ki podaljšajo bolečino.',
  header: { cta: 'Začni brezplačno' },
  hero: {
    badge: 'Mednarodna platforma za psihološko prvo pomoč',
    headline: 'Ko te boli — tu je korak za korakom.',
    subheadline: 'Brezplačni vodič: 3 napake ki podaljšajo bolečino. Vpiši email in ga prejmi takoj.',
    formBtn: 'Prenesi brezplačni vodič →',
    formNote: 'Brezplačno · Anonimno · Brez kreditne kartice',
    placeholder: 'Vpiši email',
  },
  social: [
    { title: 'Psiholog verificirano', subtitle: 'Strokovno preverjena vsebina' },
    { title: '21-dnevni program', subtitle: 'En email na dan' },
    { title: 'Anonimno', subtitle: 'Nihče ne ve. Samo ti.' },
  ],
  programs: {
    label: 'PROGRAMI',
    card1: {
      title: 'Po razhodu',
      text: 'Ko misli nanj/o ne gredo stran.',
      btn: 'Začni program →',
      btnFemale: 'Za ženske',
      btnMale: 'Za moške',
    },
    card2: { title: 'Nespečnost', text: 'Tvoja pot do mirnega spanja.', btn: 'Kmalu →' },
  },
  steps: {
    label: 'KAKO DELUJE',
    items: [
      { num: '1', title: 'Vpiši email', text: 'Dobiš brezplačni vodič takoj.' },
      { num: '2', title: 'Preberi', text: '5 minut. 3 napake ki jih verjetno delaš.' },
      { num: '3', title: 'Začni 21-dnevni program', text: '€39.90. En email na dan. Korak za korakom.' },
    ],
  },
  cta: {
    title: '21-dnevni program za okrevanje po razhodu',
    priceOld: '€110.90',
    priceNew: '€39.90',
    priceNote: '· Takojšen dostop',
    btn: 'Začni zdaj →',
    note: 'Anonimno · Takojšen dostop · Brez tveganja',
  },
  footer: {
    copy: '© 2026 Inner First Aid',
    privacy: 'Politika zasebnosti',
    terms: 'Pogoji uporabe',
    disclaimer: 'Ta program je za izobraževalne namene. Ni nadomestek za strokovno pomoč.',
  },
  cookie: {
    text: 'Uporabljamo piškotke za izboljšanje uporabniške izkušnje.',
    accept: 'Sprejmi',
    decline: 'Zavrni',
  },
};
