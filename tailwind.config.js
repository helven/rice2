import preset from './vendor/filament/support/tailwind.config.preset.js'

export default {
    presets: [preset],
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
}