/*=====================================
=            Gulp Packages            =
=====================================*/
var gulp    = require('gulp'),
concat      = require('gulp-concat'),
uglify      = require('gulp-uglify'),
svgmin      = require('gulp-svgmin'),
imagemin    = require('gulp-imagemin'),
livereload  = require('gulp-livereload'),
notify      = require("gulp-notify"),
utility     = require('gulp-util'),
watch       = require('gulp-watch'),
streamqueue = require('streamqueue'),
plumber     = require('gulp-plumber'),
shell       = require('gulp-shell'),
jshint      = require('gulp-jshint'),
gzip        = require('gulp-gzip'),
cssnext     = require('gulp-cssnext');

/*==================================
=            Base Paths            =
==================================*/
var themeBase        = './site/wp-content/themes/';
var themeName        = 'base';

// Style Path
var stylePathSrc     = themeBase + themeName + '/assets/css/base.css';
var stylePathWatch   = themeBase + themeName + '/assets/css/**/*.css';
var stylePathDest    = themeBase + themeName + '/library/css/';

// Script Path
var scriptsPathSrc   = [themeBase + themeName + '/assets/js/_lib/**/*.js', themeBase + themeName + '/assets/js/_src/**/*.js', themeBase + themeName + '/assets/js/app.js'];
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

/*===============================
=            Options            =
===============================*/
// GZIP
var gzip_options = {
  threshold: '1kb',
  gzipOptions: {
    level: 9
  }
};

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
gulp.task("stylesheets", function() {
  gulp.src(stylePathSrc)
    .pipe(plumber())
    .pipe(cssnext({
        compress: true
    }))
    .pipe(gulp.dest(stylePathDest))
    .pipe(gzip(gzip_options))
    .pipe(gulp.dest(stylePathDest))
    .pipe(livereload())
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
  .pipe(jshint())
  .pipe(jshint.reporter('default'))
  .pipe(concat('app.js', {newLine: ';'}))
  .pipe(uglify())
  .pipe(gulp.dest(scriptsPathDest))
  .pipe(livereload())
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

/*===================================
=            Watch Tasks            =
===================================*/
gulp.task('watch', function() {
  livereload.listen();

  gulp.watch(phpPath).on('change', function(file) {
    livereload.changed(file.path);
    utility.log(utility.colors.blue('PHP file changed:' + ' (' + file.path + ')'));
  });

  gulp.watch(htmlPath).on('change', function(file) {
    livereload.changed(file.path);
    utility.log(utility.colors.red('HTML file changed:' + ' (' + file.path + ')'));
  });

  gulp.watch(stylePathWatch, ['stylesheets']);
  gulp.watch(scriptsPathWatch, ['scripts']);
  gulp.watch(svgPathWatch, ['svg-opt']);
  gulp.watch(imgPathWatch, ['img-opt']);
});

/*==========================================
=            Run the Gulp Tasks            =
==========================================*/
gulp.task('default', [/*'copy',*/ 'stylesheets', 'scripts', 'watch']);
gulp.task('images', ['img-opt']);
gulp.task('svg', ['svg-opt']);
