/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './*.php',
    './app/**/*.php',
    './ae-*/**/*.php',
    './includes/**/*.php',
    './plugins/**/*.php',
    './resources/**/*.{php,html,js}',
    './ae-includes/**/*.{php,js}',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};

