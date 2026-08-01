import type { Config } from 'tailwindcss';

const config: Config = {
  content: [
    './app/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
    './lib/**/*.{js,ts,jsx,tsx,mdx}',
    './models/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#1a4d2e',
        accent: '#2d7a47',
        'light-bg': '#e8f5ed',
        'social-bg': '#0d2e1c',
        'steps-bg': '#f8fdf9',
        'card-active': '#f0faf3',
        'card-active-border': '#c8e6d0',
        'footer-border': '#c8e6d0',
        text: '#111111',
      },
      fontFamily: {
        sans: ['var(--font-inter)', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
};

export default config;
