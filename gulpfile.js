/*=====================================
=            Gulp Packages            =
=====================================*/
var gulp       = require('gulp');
var concat     = require('gulp-concat');
var uglify     = require('gulp-uglify');
var svgmin     = require('gulp-svgmin');
var imagemin   = require('gulp-imagemin');
var notify     = require("gulp-notify");
var utility    = require('gulp-util');
var watch      = require('gulp-watch');
var plumber    = require('gulp-plumber');
var cssnext    = require('gulp-cssnext');
var source     = require('vinyl-source-stream');
var gulp       = require('gulp');
var browserify = require('browserify');
var babelify   = require('babelify');
var watchify   = require('watchify');
var rename     = require('gulp-rename');
var buffer     = require('vinyl-buffer');
var livereload = require('gulp-livereload');
var streamqueue = require('streamqueue');

/*==================================
=            Base Paths            =
==================================*/
var themeBase        = './wp-content/themes/';
var themeName        = 'base';

// Style Path
var stylePathSrc     = themeBase + themeName + '/assets/css/base.css';
var stylePathWatch   = themeBase + themeName + '/assets/css/**';
var stylePathDest    = themeBase + themeName + '/library/css/';

// Script Path
var scriptsPathSrc   = themeBase + themeName + '/assets/js/';
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
// Compile, prefix, minify and move our SCSS files
gulp.task("stylesheets", function() {
  gulp.src(stylePathSrc)
    .pipe(plumber())
    .pipe(cssnext({
        compress: true
    }))
    .pipe(gulp.dest(stylePathDest))
    .pipe(gulp.dest(stylePathDest))
    .pipe(livereload())
    .pipe(notify({ message: 'Styles task complete' }));
});

// Compile JS
// Compile (in order), concatenate, minify, rename and move our JS files
function handleErrors() {
  var args = Array.prototype.slice.call(arguments);
  notify.onError({
    title: 'Compile Error',
    message: '<%= error.message %>'
  }).apply(this, args);
  this.emit('end'); // Keep gulp from hanging on this task
}

function buildScript(file, watch) {
  var props = {
    entries: [scriptsPathSrc + '/app.js'],
    debug : true,
    transform:  [babelify.configure({presets: ['es2015']})]
  };

  // watchify() if watch requested, otherwise run browserify() once
  var bundler = watch ? watchify(browserify(props)) : browserify(props);

  function rebundle() {
    var stream = bundler.bundle();
    return stream
      .on('error', handleErrors)
      .pipe(source(file))
      .pipe(gulp.dest(scriptsPathDest))
      // If you also want to uglify it
      .pipe(buffer())
      .pipe(uglify())
      .pipe(rename('app.min.js'))
      .pipe(gulp.dest(scriptsPathDest))
      .pipe(livereload())
      .pipe(notify({ message: 'Scripts task complete' }));
  }

  // listen for an update and run rebundle
  bundler.on('update', function() {
    rebundle();
    utility.log('Rebundle...');
  });

  // run it once the first time buildScript is called
  return rebundle();
}

gulp.task('scripts', function() {
  return buildScript('app.js', false); // this will run once because we set watch to false
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

  gulp.watch(stylePathWatch, ['stylesheets']);
  gulp.watch(scriptsPathWatch, ['scripts']);
  gulp.watch(svgPathWatch, ['svg-opt']);
  gulp.watch(imgPathWatch, ['img-opt']);
});

/*==========================================
=            Run the Gulp Tasks            =
==========================================*/
gulp.task('default', ['stylesheets', 'scripts', 'watch']);
gulp.task('build', ['stylesheets', 'scripts']);
gulp.task('images', ['img-opt']);
gulp.task('svg', ['svg-opt']);
