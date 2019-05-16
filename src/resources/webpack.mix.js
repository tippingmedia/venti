const mix = require('laravel-mix');
let SVGSpritemapPlugin = require('svg-spritemap-webpack-plugin');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// use .extract(['vue'])  if using Vuejs
mix
  //.js("lib/venti.js", "./js")
  .js("lib/VentiCalendar.js", "./js")
  .js("lib/VentiInput.js", "./js")
  .sass("sass/venti.scss", "./css")
  .options({
    postCss: [require("postcss-custom-properties")]
  });

// if (mix.inProduction()) {
//     mix.version();
// }

// https://laracasts.com/discuss/channels/laravel/help-needed-svg-sprites-with-laravelmix?page=1
if (mix.inProduction()) {
    mix.webpackConfig({
        plugins: [
            new SVGSpritemapPlugin({
                src: './img/icons/*.svg',
                filename: './img/_icons.svg',
                prefix: 'icon--',
                svgo: { removeTitle: true }
            })
        ]
    });
}
