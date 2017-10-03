var gulp        = require('gulp'),
    fs          = require('fs'),
    $           = require('gulp-load-plugins')(),
    pngquant    = require('imagemin-pngquant'),
    eventStream = require('event-stream');

// Include Path for Scss
var includesPaths = [
    './src/scss'
];

// Source directory
var srcDir = {
    scss: [
        './src/scss/**/*.scss'
    ],
    js: [
        './src/js/**/*.js',
        '!./src/js/**/_*.js'
    ],
    jsHint: [
        './src/js/**/*.js'
    ],
    jshintrc: [
        './.jshintrc'
    ],
    img: [
        './src/img/**/*'
    ]

};
// Destination directory
var destDir = {
    scss: './assets/css',
    js: './assets/js',
    img: './assets/img'
};

// Sass
gulp.task('sass', function () {

  return gulp.src(srcDir.scss)
    .pipe($.plumber({
        errorHandler: $.notify.onError('<%= error.message %>')
    }))
    .pipe($.sassBulkImport())
    .pipe($.sourcemaps.init())
    .pipe($.sass({
      errLogToConsole: true,
      outputStyle    : 'compressed',
      sourceComments : 'normal',
      sourcemap      : true,
      includePaths   : includesPaths
    }))
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest(destDir.scss));
});


// Minify All
gulp.task('jsconcat', function () {
  return gulp.src(srcDir.js)
    .pipe($.sourcemaps.init({
      loadMaps: true
    }))
    .pipe($.include())
    .pipe($.uglify())
    .on('error', $.util.log)
    .pipe($.sourcemaps.write('./map'))
    .pipe(gulp.dest(destDir.js));
});


// JS Hint
gulp.task('jshint', function () {
  return gulp.src(srcDir.jsHint)
    .pipe($.plumber())
    .pipe($.jshint(srcDir.jshintrc))
    .pipe($.jshint.reporter('jshint-stylish'));
});

// JS task
gulp.task('js', ['jshint', 'jsconcat']);


// Build Libraries.
gulp.task('copylib', function () {
  // pass gulp tasks to event stream.
  // return eventStream.merge(
  // );
});

// Image min
gulp.task('imagemin', function () {
  return gulp.src(srcDir.img)
    .pipe($.imagemin({
      progressive: true,
      svgoPlugins: [{removeViewBox: false}],
      use        : [pngquant()]
    }))
    .pipe(gulp.dest(destDir.img));
});


// watch
gulp.task('watch', function () {
  // Make SASS
  gulp.watch(srcDir.scss, ['sass']);
  // Uglify all
  gulp.watch(srcDir.jsHint, ['js']);
  // Minify Image
  gulp.watch(srcDir.img, ['imagemin']);
});

// Build
gulp.task('build', ['copylib', 'js', 'sass', 'imagemin']);

// Default Tasks
gulp.task('default', ['watch']);

