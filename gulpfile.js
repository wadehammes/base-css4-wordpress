/*=====================================
=            Gulp Packages            =
=====================================*/
require('es6-promise').polyfill();

var gulp    = require('gulp'),
fs          = require('fs'),
concat      = require('gulp-concat'),
uglify      = require('gulp-uglify'),
svgmin      = require('gulp-svgmin'),
imagemin    = require('gulp-imagemin'),
notify      = require("gulp-notify"),
utility     = require('gulp-util'),
watch       = require('gulp-watch'),
streamqueue = require('streamqueue'),
plumber     = require('gulp-plumber'),
shell       = require('gulp-shell'),
sourcemaps  = require('gulp-sourcemaps'),
postcss     = require('gulp-postcss'),
browserSync = require('browser-sync').create();

// Read our Settings Configuration
var settings = JSON.parse(fs.readFileSync('./settings.json'));

/*==================================
=            Base Paths            =
==================================*/
var themeBase        = './wp-content/themes/';
var themeName        = 'base';

// Style Path
var stylePathSrc     = themeBase + themeName + '/assets/css/base.css';
var stylePathWatch   = themeBase + themeName + '/assets/css/**/*.css';
var stylePathDest    = themeBase + themeName + '/library/css/';

// Script Path
var scriptsPathSrc   = [themeBase + themeName + '/assets/js/_lib/**/*.js', themeBase + themeName + '/assets/js/_src/**/*.js', themeBase + themeName + '/assets/js/application-new.js'];
var scriptsPathWatch = themeBase + themeName + '/assets/js/**/*.js';
var scriptsPathDest  = themeBase + themeName + '/library/js/';

// Sprites Path
var svgPathWatch     = themeBase + themeName + '/assets/svg/*.svg';
var svgDest          = themeBase + themeName + '/library/svg';

// Image Path
var imgPathWatch     = themeBase + themeName + '/assets/img/*';
var imgDest          = themeBase + themeName + '/library/img';

// PHP Paths
var phpPath          = themeBase + themeName + '/**/*.php';

/*=============================
=            Tasks            =
=============================*/
// Copy bower files into our assets
gulp.task('copy', function() {
  gulp.src([
    /* add bower src files here if you include a bower.json */
  ])
  .pipe(gulp.dest(devBase + '/js/_lib/'));
});

// Compile, prefix, minify and move our SCSS files
gulp.task('stylesheets', function () {
  var processors = [
    require("postcss-import")(),
    require("postcss-url")(),
    require('postcss-utilities')(),
    require("precss")(),
    require("postcss-cssnext")(),
    require("css-mqpacker")(),
    require("cssnano")({
      discardComments: {
        removeAll: true
      },
      filterPlugins: false,
      discardEmpty: false,
      autoprefixer: false
    }),
    require("postcss-reporter")()
  ];
  return gulp.src(stylePathSrc)
    .pipe(plumber())
    .pipe( sourcemaps.init() )
    .pipe(postcss(processors))
    .pipe( sourcemaps.write('.') )
    .pipe(gulp.dest(stylePathDest))
    .pipe(browserSync.stream())
    .pipe(notify({ message: 'Styles task complete' }));
});

// Compile (in order), concatenate, minify, rename and move our JS files
gulp.task('scripts', function() {
  return streamqueue({ objectMode: true },
    gulp.src(themeBase + themeName + '/assets/js/_lib/**/*.js'),
    gulp.src(themeBase + themeName + '/assets/js/_src/**/*.js'),
    gulp.src(themeBase + themeName + '/assets/js/app.js')
  )
  .pipe(plumber())
  .pipe(concat('app.js', {newLine: ';'}))
  .pipe(uglify())
  .pipe(gulp.dest(scriptsPathDest))
  .pipe(browserSync.stream())
  .pipe(notify({ message: 'Scripts task complete' }));
});

/*========================================
=            Standalone Tasks            =
========================================*/
// Optimize images
gulp.task('img-opt', function () {
  return gulp.src(imgPathWatch)
  .pipe(imagemin({
    progressive: true
    }))
  .pipe(gulp.dest(imgDest))
  .pipe(notify({ message: 'Images task complete' }));
});

// Optimize our SVGS
gulp.task('svg-opt', function () {
  return gulp.src(svgPathWatch)
  .pipe(svgmin({
    plugins: [
    {removeEmptyAttrs: false},
    {removeEmptyNS: false},
    {cleanupIDs: false},
    {unknownAttrs: false},
    {unknownContent: false},
    {defaultAttrs: false},
    {removeTitle: true},
    {removeDesc: true},
    {removeDoctype: true}
    ],
    }))
  .pipe(gulp.dest(svgDest))
  .pipe(notify({ message: 'SVG task complete' }));
});

// Browser Sync
gulp.task('serve', ['stylesheets', 'scripts'], function() {
    browserSync.init({
        proxy: settings.devUrl
    });

    gulp.watch(stylePathWatch, ['stylesheets']);
    gulp.watch(scriptsPathWatch, ['scripts']);
    gulp.watch(phpPath).on('change', browserSync.reload);
});

/*===================================
=            Watch Tasks            =
===================================*/
gulp.task('watch-images', function() {
  gulp.watch(svgPathWatch, ['svg-opt']);
  gulp.watch(imgPathWatch, ['img-opt']);
});

/*==========================================
=            Run the Gulp Tasks            =
==========================================*/
gulp.task('default', ['stylesheets', 'scripts', 'watch-images', 'serve']);
gulp.task('build', ['stylesheets', 'scripts']);
gulp.task('images', ['img-opt']);
gulp.task('svg', ['svg-opt']);
