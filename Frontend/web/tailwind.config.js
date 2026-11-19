import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        '../../Backend/vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        '../../Backend/storage/framework/views/*.php',
        '../../Backend/resources/views/**/*.blade.php',
        './resources/**/*.js',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
