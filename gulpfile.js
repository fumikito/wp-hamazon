var gulp        = require('gulp'),
    fs          = require('fs'),
    $           = require('gulp-load-plugins')(),
    pngquant    = require('imagemin-pngquant'),
    eventStream = require('event-stream'),
    webpack       = require( 'webpack-stream' ),
    webpackBundle = require( 'webpack' ),
    named         = require( 'vinyl-named' );

// Include Path for Scss
var includesPaths = [
    './src/scss'
];

// Source directory
var srcDir = {
    scss: [
        'src/scss/**/*.scss'
    ],
    js: [
        'src/js/**/*.js',
        '!src/js/**/_*.js',
        '!src/js/editor/**/*.js'
    ],
    jsHint: [
        'src/js/**/*.js'
    ],
    jshintrc: [
        '.jshintrc'
    ],
    img: [
        'src/img/**/*'
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
    .pipe($.autoprefixer({
      browsers: ['last 2 version', 'iOS >= 8.1', 'Android >= 4.4']
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


// eslint
gulp.task( 'eslint', function() {
  return gulp.src([
    './src/js/**/*.js',
    './src/js/**/*.jsx'
  ])
    .pipe( $.eslint({ useEslintrc: true }) )
    .pipe( $.eslint.format() );
});

// JS task
gulp.task('js', ['eslint', 'jsconcat']);

// JSX
gulp.task('babel-jsx', function(){
  return gulp.src([
    './src/js/editor/hamazon-editor.jsx',
    './src/js/editor/hamazon-block.jsx'
  ])
    .pipe( $.plumber({
      errorHandler: $.notify.onError( '<%= error.message %>' )
    }) )
    .pipe( named( function( file ) {
      return file.relative.replace( /\.[^.]+$/, '' );
    }) )
    .pipe( webpack({
      mode: 'production',
      devtool: 'source-map',
      resolve: {
        extensions:  ['.js', '.jsx']
      },
      module: {
        rules: [
          {
            test: /\.jsx?$/,
            exclude: /(node_modules|bower_components)/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: [
                  '@babel/preset-env',
                  '@babel/preset-react'
                ]
              }
            }
          }
        ]
      }
    }, webpackBundle ) )
    .pipe( gulp.dest( './assets/js/editor' ) );
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
  // Babel
  gulp.watch('src/js/**/*.jsx', ['babel-jsx', 'eslint']);
  // Minify Image
  gulp.watch(srcDir.img, ['imagemin']);
});

// Build
gulp.task('build', ['js', 'sass', 'imagemin', 'babel-jsx']);

// Default Tasks
gulp.task('default', ['watch']);

