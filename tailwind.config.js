import colors from 'tailwindcss/colors'
import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'

export default {
    darkMode: 'class',
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                danger: colors.rose,
                primary: colors.teal,
                success: colors.green,
                warning: colors.amber,
                info: colors.blue,
                gray: colors.zinc,
            },
        },
    },
    plugins: [
        forms,
        typography,
    ],
    corePlugins: {
        preflight: false,
    },
}
