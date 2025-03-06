import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.css',
    ],

    theme: {
        extend: {
            colors: {
                transparent: 'transparent',
                current: 'currentColor',
                black: colors.black,
                white: colors.white,
                gray: colors.neutral,
                'black-background': '#0a0a0a',
                'gray-background': '#212121',
                'orange': '#e8592a',
                'orange-border': '#fa6a39',
                'gray': '#545454',
            },
            // fontFamily: {
            //     sans: ['Albert Sans', ...defaultTheme.fontFamily.sans],
            // },
            fontSize: {
                '2xs': '.625rem',
            }, 
        },
    },

    plugins: [forms,],
};
