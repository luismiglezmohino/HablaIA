import tailwindcssAnimate from 'tailwindcss-animate'

/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      // HablaIA Design System (ADR-011)
      colors: {
        primary: {
          50: '#eef2ff',
          100: '#e0e7ff',
          200: '#c7d2fe',
          300: '#a5b4fc',
          400: '#818cf8',
          500: '#6366f1',
          600: '#4f46e5',
          700: '#4338ca',
          800: '#3730a3',
          900: '#312e81',
          950: '#1e1b4b',
        },
        accent: {
          50: '#fdf4ff',
          100: '#fae8ff',
          200: '#f5d0fe',
          300: '#f0abfc',
          400: '#e879f9',
          500: '#d946ef',
          600: '#c026d3',
          700: '#a21caf',
          800: '#86198f',
          900: '#701a75',
          950: '#4a044e',
        },
        surface: {
          50: '#fafaf9',
          100: '#f5f5f4',
          200: '#e7e5e4',
          300: '#d6d3d1',
        },
        // High contrast colors for accessibility
        accessible: {
          text: '#1c1917', // stone-900, contrast ratio 15.5:1
          textLight: '#57534e', // stone-600, contrast ratio > 4.5:1
          background: '#fafaf9', // stone-50, warm off-white
          focus: '#4f46e5', // indigo-600
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
      },
      // Minimum touch target size for accessibility (44x44px)
      minHeight: {
        touch: '44px',
      },
      minWidth: {
        touch: '44px',
      },
      // Focus ring styles for keyboard navigation
      ringWidth: {
        focus: '3px',
      },
      // Custom shadows for depth (ADR-011)
      boxShadow: {
        card: '0 4px 12px -2px rgb(0 0 0 / 0.08), 0 2px 4px -1px rgb(0 0 0 / 0.04)',
        'card-hover': '0 12px 28px -6px rgb(0 0 0 / 0.14), 0 6px 12px -4px rgb(0 0 0 / 0.06)',
        glow: '0 4px 24px -4px rgb(99 102 241 / 0.5)',
        'glow-accent': '0 4px 24px -4px rgb(217 70 239 / 0.4)',
        soft: '0 1px 4px 0 rgb(0 0 0 / 0.04)',
      },
    },
  },
  plugins: [tailwindcssAnimate],
}
