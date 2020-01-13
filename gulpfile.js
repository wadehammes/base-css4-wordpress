/*=====================================
=            Gulp Packages            =
=====================================*/
require("es6-promise").polyfill();

var gulp = require("gulp"),
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
var settings = JSON.parse(fs.readFileSync("./settings.json"));

// Process arguments passed to Gulp CLI (like gulp --production)
var args = minimist(process.argv);

var config = {
	production: args.production
};

/*==================================
=            Base Paths            =
==================================*/
var themeBase = "./wp-content/themes/";
var themeName = "base";

// Style Path
var stylePathSrc = themeBase + themeName + "/assets/css/base.css";
var stylePathWatch = themeBase + themeName + "/assets/css/**/*.css";
var stylePathDest = themeBase + themeName + "/library/css/";

// Script Path
var scriptsPathWatch = themeBase + themeName + "/assets/js/**/*.js";
var scriptsPathDest = themeBase + themeName + "/library/js/";

// Sprites Path
var svgPathWatch = themeBase + themeName + "/assets/svg/*.svg";
var svgSymbolsPathWatch = themeBase + themeName + "/assets/symbols/*.svg";
var svgDest = themeBase + themeName + "/library/svg";
var svgCopyDest = themeBase + themeName + "/library/svgs";

// PHP Paths
var phpPath = themeBase + themeName + "/**/*.php";

/*=============================
=            Tasks            =
=============================*/
// Compile, prefix, minify and move our CSS files
gulp.task("stylesheets", function() {
	var processors = [
		require("postcss-import")(),
		require("postcss-url")(),
		require("postcss-utilities")(),
		require("postcss-mixins")(),
		require("precss")(),
		require("postcss-preset-env")({
			stage: 2,
			autoprefixer: { grid: true }
		}),
		require("postcss-namespace")(),
		require("css-mqpacker")(),
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

gulp.task("symbols", function() {
	return gulp
		.src(svgSymbolsPathWatch)
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
		.pipe(
			config.production ? noop({ objectMode: true }) : browserSync.stream()
		);
});

gulp.task("svg-copy", function() {
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
		.pipe(gulp.dest(svgCopyDest))
		.pipe(
			config.production ? noop({ objectMode: true }) : browserSync.stream()
		);
});

/*========================================
=            Standalone Tasks            =
========================================*/

// Browser Sync
gulp.task(
	"serve",
	["stylesheets", "scripts", "symbols", "svg-copy"],
	function() {
		browserSync.init({
			files: "wp-content/themes/base/*",
			open: "external",
			host: "sky.local",
			port: 1111,
			proxy: settings.devUrl,
			https: {
				key: "../../../Keys/sky.local.key",
				cert: "../../../Keys/sky.local.crt"
			}
		});

		gulp.watch(stylePathWatch, ["stylesheets"]);
		gulp.watch(scriptsPathWatch, ["scripts"]);
		gulp.watch(svgPathWatch, ["symbols", "svg-copy"]);
		gulp.watch([svgDest, svgCopyDest]).on("change", browserSync.reload);
		gulp.watch(phpPath).on("change", browserSync.reload);
		gulp.watch(stylePathDest + "base.css").on("change", browserSync.reload);
	}
);

/*==========================================
=            Run the Gulp Tasks            =
==========================================*/
gulp.task("default", [
	"stylesheets",
	"scripts",
	"symbols",
	"svg-copy",
	"serve"
]);
gulp.task("build", ["stylesheets", "scripts", "symbols", "svg-copy"]);
