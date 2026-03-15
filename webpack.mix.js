const mix = require("laravel-mix");
const path = require("path");
const webpack = require("webpack");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.webpackConfig({
    plugins: [
        new webpack.DefinePlugin({
            "process.env": {
                MIX_JITSI_APP_ID: JSON.stringify(process.env.MIX_JITSI_APP_ID),
            },
            // Required by Vue 3 esm-bundler to enable tree-shaking and suppress
            // the "feature flag not explicitly defined" console warning.
            __VUE_OPTIONS_API__: JSON.stringify(true),
            __VUE_PROD_DEVTOOLS__: JSON.stringify(false),
            __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false),
        }),
    ],
});

mix.alias({
    "@": path.join(__dirname, "resources/js"),
});

mix.js("resources/js/app.js", "public/js/App.js")
    .css("resources/js/assets/App.css", "public/css/App.css")
    .vue()
    .version()
    .override((config) => {
        delete config.watchOptions;
    })
    .disableNotifications();
