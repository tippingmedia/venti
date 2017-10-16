/*
 * Gulp install plugins
 * @package.json
 */
const gulp = require('gulp');
const gulpLoadPlugins = require('gulp-load-plugins');
const $ = gulpLoadPlugins();
const cleanCSS = require('gulp-clean-css');
const sass = require('gulp-ruby-sass');
const babel = require('gulp-babel');

require("babel-core").transform("code", {
  presets: ["es2015"]
});


/*
 * Paths for watch,process & bower
 */
var paths = {
    watch: {
        scripts: ['lib/**/*.js'],
        styles:['sass/**/*.scss'],
    },
    process: {
        scripts:[
            'lib/venti/venti.js',
            'lib/venti/ventiEventsClass.js',
            'lib/venti/ventiInputClass.js',
            'lib/venti/ventiScheduleClass.js',
            'lib/venti/ventiModalClass.js',
            'lib/venti/ventiElementEditor.js',
            'lib/venti/ventiCalendarClass.js',
            'lib/venti/ventiLocationClass.js',
        ],
        sass:['sass/**/*']
    }
};




/*
 * jsHint
 */
gulp.task('jshint', function () {
    console.log('–:::– JSHINT –:::–');
    return gulp.src(['js/lib/**/*.js'])
        .pipe($.jshint())
        .pipe($.jshint.reporter('jshint-stylish'))
});







/*
 * Contact script files to master.js
 */
gulp.task('scripts', function(){
    console.log('–:::– SCRIPTS –:::–');
    return gulp.src(paths.process.scripts)
        .pipe($.changed('js/lib/**/*.js'))
        .pipe($.concat('venti.js'))
        .pipe(babel({
            presets: ['es2015']
        }))
        .pipe(gulp.dest('js/'));
});




/*
 * Minify master.js => master.min.js
 */
gulp.task('compress',['scripts'],function(){
    return gulp.src('js/venti.js')
        .pipe($.jsmin())
        .pipe($.rename({suffix: '.min'}))
        .pipe(gulp.dest('js/'));
});



/*
 * Notification when script process is done.
 */
gulp.task('notify',['compress'],function(){
    return gulp.src("notify.ext")
     .pipe($.notify({
        "title": "Venti Craft Plugin",
        //"subtitle": "Project web site",
        "message": "Script processing successful!",
        "sound": "Morse", // case sensitive
        "onLast": true,
        "wait": true
    }));
});


/*
 * Notification when script process is done.
 */
gulp.task('notify-css',['styles'],function(){
    return gulp.src("notify.ext")
     .pipe($.notify({
        "title": "Venti Craft Plugin",
        //"subtitle": "Project web site",
        "message": "CSS processing successful!",
        "sound": "Morse", // case sensitive
        "onLast": true,
        "wait": true
    }));
});


/*
 * sass build
 */
gulp.task('sass-app',function(){
    console.log('–:::SASS:::–');
     return sass('sass/')
        .on('error', function (err) {
            console.error('Error!', err.message);
     })
        .pipe(gulp.dest('css/src'));
});



/*
 * Minify & autoprefix styles
 */
gulp.task('styles',['sass-app'],function(){
    console.log('–:::STYLES:::–');
    return gulp.src('css/src/**/*')
        .pipe($.concat('venti.css'))
        .pipe(cleanCSS(function(details) {
            console.log(details.name + ': ' + details.stats.originalSize);
            console.log(details.name + ': ' + details.stats.minifiedSize);
        }))
        .pipe(gulp.dest('css'));
});



/*
 * Watch 'default'
 */
gulp.task('default', function() {
    gulp.watch(paths.watch.scripts, ['scripts','compress','notify']);
    gulp.watch(paths.watch.styles, ['sass-app','styles','notify-css']);
    //gulp.watch(paths.images, ['images']);
});
