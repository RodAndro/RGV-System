/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#eff8fd',
          100: '#d8eefa',
          500: '#0f6f9d',
          600: '#0a5c83',
          700: '#084a69',
          900: '#053049',
        },
      },
    },
  },
  plugins: [],
};
