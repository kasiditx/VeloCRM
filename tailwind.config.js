import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    50: 'var(--color-primary-50, #f5f3ff)',
                    100: 'var(--color-primary-100, #ede9fe)',
                    200: 'var(--color-primary-200, #ddd6fe)',
                    300: 'var(--color-primary-300, #c4b5fd)',
                    400: 'var(--color-primary-400, #a78bfa)',
                    500: 'var(--color-primary, #4f46e5)',
                    600: 'var(--color-primary-600, #4f46e5)',
                    700: 'var(--color-primary-700, #4338ca)',
                    800: 'var(--color-primary-800, #3730a3)',
                    900: 'var(--color-primary-900, #1e1b4b)',
                    950: 'var(--color-primary-950, #0f172a)',
                },
            },
            fontFamily: {
                sans: ['Inter', 'Prompt', ...defaultTheme.fontFamily.sans],
                display: ['Inter', 'Prompt', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
