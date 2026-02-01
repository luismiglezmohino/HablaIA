/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      // WCAG 2.1 AA compliant color palette
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
          950: '#172554',
        },
        // High contrast colors for accessibility
        accessible: {
          text: '#1f2937', // gray-800, contrast ratio > 7:1
          textLight: '#4b5563', // gray-600, contrast ratio > 4.5:1
          background: '#ffffff',
          focus: '#2563eb', // primary-600
        },
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
    },
  },
  plugins: [],
}
