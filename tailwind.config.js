import defaultTheme from 'tailwindcss/defaultTheme'

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.vue',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                outfit: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#2563eb',   // blue-600 (VISIBLE in light mode)
                    dark: '#3b82f6',      // blue-500 (VISIBLE in dark mode)
                },
            },
        },
    },
}
