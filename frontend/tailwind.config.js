/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: {
          50:  '#f5f3ff',
          100: '#ede9fe',
          200: '#ddd6fe',
          300: '#c4b5fd',
          400: '#a78bfa',
          500: '#8b5cf6',
          600: '#7c3aed',
          700: '#6d28d9',
          800: '#5b21b6',
          900: '#4c1d95',
        },
        dark: {
          900: '#09090f',
          800: '#0d0d1a',
          700: '#111127',
          600: '#161630',
          500: '#1c1c3a',
        },
        surface: {
          950: '#07070f',
          900: '#09090f',
          800: '#0d0d1a',
          700: '#111127',
          600: '#161630',
          500: '#1c1c3a',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'glow-primary': 'radial-gradient(ellipse at center, rgba(124,58,237,0.15) 0%, transparent 70%)',
      },
      boxShadow: {
        'glow-sm': '0 0 12px rgba(124,58,237,0.25)',
        'glow': '0 0 24px rgba(124,58,237,0.3)',
        'glow-lg': '0 0 40px rgba(124,58,237,0.35)',
        'card': '0 1px 3px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.05)',
        'card-hover': '0 4px 16px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.08)',
      },
      animation: {
        'fade-in': 'fadeIn 0.2s ease-out',
        'slide-up': 'slideUp 0.3s ease-out',
        'pulse-slow': 'pulse 3s ease-in-out infinite',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        slideUp: {
          '0%': { opacity: '0', transform: 'translateY(8px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
      },
    },
  },
  plugins: [],
}
