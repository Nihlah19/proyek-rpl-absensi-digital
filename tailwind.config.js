/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './web/app/views/**/*.php',
    './web/app/helpers/**/*.php',
    './web/app/core/*.php',
    './web/app/init.php',
    './web/app/views/templates/*.php',
    './web/app/views/dashboard/*.php',
    './web/app/views/auth/*.php',
    './web/app/views/home/*.php',
    './web/app/views/errors/*.php',
    './web/app/views/jadwal/*.php',
    './web/app/views/izin/*.php',
    './web/app/views/catatan/*.php',
    './web/app/views/guru/*.php',
    './web/app/views/absensi/*.php',
    './web/app/views/chatbot/*.php',
    './src/input.css',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0F7A4A',
        'primary-dark': '#075C38',
        'primary-light': '#14B8A6',
        secondary: '#F59E0B',
        accent: '#EA580C',
        success: '#059669',
        danger: '#DC2626',
        warning: '#D97706',
        info: '#0EA5E9',
        dark: '#172033', // Replaced with Text color
        muted: '#667085',
        soft: '#EAF7F0',
        bglight: '#F7F9F8',
        white: '#FFFFFF',
        slate: {
            50: '#f8fafc',
            100: '#f1f5f9',
            200: '#e2e8f0',
            300: '#cbd5e1',
            400: '#94a3b8',
            500: '#64748B',
            600: '#475569',
            700: '#334155',
            800: '#1e293b',
            900: '#0f172a',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      },
      boxShadow: {
        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
        'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.07)',
      }
    },
  },
  plugins: [],
}
