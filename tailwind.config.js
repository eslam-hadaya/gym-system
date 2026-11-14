import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.vue',
    './resources/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50:  'var(--brand-50)',
          100: 'var(--brand-100)',
          200: 'var(--brand-200)',
          300: 'var(--brand-300)',
          400: 'var(--brand-400)',
          500: 'var(--brand-500)',
          600: 'var(--brand-600)',
          700: 'var(--brand-700)',
          800: 'var(--brand-800)',
          900: 'var(--brand-900)',
        },
        surface: {
          base:     'var(--bg-base)',
          elevated: 'var(--bg-elevated)',
          soft:     'var(--bg-soft)',
        },
        ink: {
          primary:   'var(--text-primary)',
          secondary: 'var(--text-secondary)',
          muted:     'var(--text-muted)',
        },
        glass: {
          bg:          'var(--glass-bg)',
          'bg-hover':  'var(--glass-bg-hover)',
          border:      'var(--glass-border)',
        },
        line: {
          soft:   'var(--border-soft)',
          strong: 'var(--border-strong)',
        },
      },
      borderRadius: {
        sm: 'var(--radius-sm)',
        md: 'var(--radius-md)',
        lg: 'var(--radius-lg)',
        xl: 'var(--radius-xl)',
      },
      boxShadow: {
        sm:   'var(--shadow-sm)',
        md:   'var(--shadow-md)',
        glow: 'var(--shadow-glow-brand)',
      },
      backdropBlur: {
        glass: 'var(--glass-blur)',
      },
      fontFamily: {
        sans: ['Cairo', 'Tajawal', ...defaultTheme.fontFamily.sans],
      },
    },
  },
  plugins: [],
};
