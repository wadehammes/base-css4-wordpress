/*=====================================
=            Gulp Packages            =
=====================================*/
require("es6-promise").polyfill();

const gulp = require("gulp"),
  fs = require("fs"),
  concat = require("gulp-concat"),
  uglify = require("gulp-uglify"),
  svgmin = require("gulp-svgmin"),
  noop = require("through2"),
  minimist = require("minimist"),
  streamqueue = require("streamqueue"),
  plumber = require("gulp-plumber"),
  sourcemaps = require("gulp-sourcemaps"),
  postcss = require("gulp-postcss"),
  svgstore = require("gulp-svgstore"),
  babel = require("gulp-babel"),
  browserSync = require("browser-sync").create();

// Read our Settings Configuration
const settings = JSON.parse(fs.readFileSync("./settings.json"));

// Process arguments passed to Gulp CLI (like gulp --production)
const args = minimist(process.argv);

const config = {
  production: args.production
};

/*==================================
=            Base Paths            =
==================================*/
const themeBase = "./wp-content/themes/";
const themeName = "base";

// Style Path
const stylePathSrc = themeBase + themeName + "/assets/css/base.css";
const stylePathWatch = themeBase + themeName + "/assets/css/**/*.css";
const stylePathDest = themeBase + themeName + "/library/css/";

// Script Path
const scriptsPathWatch = themeBase + themeName + "/assets/js/**/*.js";
const scriptsPathDest = themeBase + themeName + "/library/js/";

// Sprites Path
const svgPathWatch = themeBase + themeName + "/assets/svg/*.svg";
const svgDest = themeBase + themeName + "/library/svg";

// PHP Paths
const phpPath = themeBase + themeName + "/**/*.php";

/*=============================
=            Tasks            =
=============================*/
// Compile, prefix, minify and move our CSS files
gulp.task("stylesheets", function() {
  const processors = [
    require("postcss-import")(),
    require("postcss-url")(),
    require("postcss-utilities")(),
    require("postcss-mixins")(),
    require("precss")(),
    require("postcss-preset-env")({
      stage: 2,
      autoprefixer: false
    }),
    require("postcss-namespace")(),
    require("cssnano")({
      discardComments: {
        removeAll: true
      },
      filterPlugins: false,
      discardEmpty: false,
      autoprefixer: false
    }),
    require("postcss-reporter")({ clearReportedMessages: true })
  ];
  return gulp
    .src(stylePathSrc)
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(postcss(processors))
    .pipe(sourcemaps.write("."))
    .pipe(gulp.dest(stylePathDest))
    .pipe(
      config.production
        ? noop({ objectMode: true })
        : browserSync.reload({ stream: true })
    );
});

// Compile (in order), concatenate, minify, rename and move our JS files
gulp.task("scripts", function() {
  return streamqueue(
    { objectMode: true },
    gulp.src(themeBase + themeName + "/assets/js/_lib/**/*.js"),
    gulp.src(themeBase + themeName + "/assets/js/_src/**/*.js"),
    gulp.src(themeBase + themeName + "/assets/js/application.js")
  )
    .pipe(plumber())
    .pipe(babel({ presets: ["@babel/preset-env"] }))
    .pipe(concat("application.js", { newLine: ";" }))
    .pipe(uglify())
    .pipe(gulp.dest(scriptsPathDest))
    .pipe(
      config.production
        ? noop({ objectMode: true })
        : browserSync.reload({ stream: true })
    );
});

gulp.task("svgs", function() {
  return gulp
    .src(svgPathWatch)
    .pipe(plumber())
    .pipe(
      svgmin({
        plugins: [
          { removeEmptyAttrs: false },
          { removeEmptyNS: false },
          { cleanupIDs: false },
          { unknownAttrs: false },
          { unknownContent: false },
          { defaultAttrs: false },
          { removeTitle: true },
          { removeDesc: true },
          { removeDoctype: true }
        ]
      })
    )
    .pipe(svgstore({ inlineSvg: true }))
    .pipe(gulp.dest(svgDest))
    .pipe(config.production ? noop({ objectMode: true }) : browserSync.stream())
    .on("end", function() {
      fs.renameSync(svgDest + "/svg.svg", svgDest + "/sprite.svg");
    });
});

/*========================================
=            Standalone Tasks            =
========================================*/

// Browser Sync
gulp.task("serve", ["stylesheets", "scripts", "svgs"], function() {
  const files = [stylePathWatch, scriptsPathWatch, phpPath];

  browserSync.init(files, {
    proxy: settings.devUrl
  });

  gulp.watch(stylePathWatch, ["stylesheets"]);
  gulp.watch(scriptsPathWatch, ["scripts"]);
  gulp.watch(scriptsPathWatch, ["svgs"]);
  gulp.watch(svgDest).on("change", browserSync.reload);
  gulp.watch(phpPath).on("change", browserSync.reload);
  gulp.watch(stylePathDest + "base.css").on("change", browserSync.reload);
});

/*==========================================
=            Run the Gulp Tasks            =
==========================================*/
gulp.task("default", ["stylesheets", "scripts", "svgs", "serve"]);
gulp.task("build", ["stylesheets", "scripts", "svgs"]);
