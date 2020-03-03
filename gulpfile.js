const gulp    = require( 'gulp' );
const $ = require( 'gulp-load-plugins' )();
const mozjpeg = require( 'imagemin-mozjpeg' );
const pngquant = require( 'imagemin-pngquant' );
const webpack = require( 'webpack-stream' );
const webpackBundle = require( 'webpack' );
const named = require( 'vinyl-named' );

// Sass
gulp.task( 'scss', function () {

	return gulp.src( './src/scss' )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		// .pipe( $.sassGlob() )
		.pipe( $.sourcemaps.init() )
		.pipe( $.sass( {
			errLogToConsole: true,
			outputStyle    : 'compressed',
			sourceComments : 'normal',
			sourcemap      : true,
			includePaths   : [ './src/scss' ]
		} ) )
		.pipe( $.autoprefixer() )
		.pipe( $.sourcemaps.write( './map' ) )
		.pipe( gulp.dest( 'assets/css' ) );
} );

// Stylelint
gulp.task( 'stylelint', function( done ) {
	return gulp.src( './src/scss/**/*.scss' )
		.pipe( $.stylelint( {
			failAfterError: false,
			reporters: [
				{
					formatter: 'string',
					console: true,
				},
			],
		} ) );
} );


// eslint
gulp.task( 'eslint', function () {
	return gulp.src( [
		'./src/js/**/*.js',
		'./src/js/**/*.jsx'
	] )
		.pipe( $.eslint( { useEslintrc: true } ) )
		.pipe( $.eslint.format() );
} );

// Bundle JavaScripts.
gulp.task( 'js:bundle', function () {
	const tmp = {};
	return gulp.src( [ './src/js/**/*.jsx', './src/js/**/*.js' ] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( named() )
		.pipe( $.rename( function ( path ) {
			tmp[ path.basename ] = path.dirname;
		} ) )
		.pipe( webpack( {
			mode  : 'production',
			module: {
				rules: [
					{
						test   : /\.jsx?$/,
						exclude: /(node_modules)/,
						use    : {
							loader : 'babel-loader',
							options: {
								presets: [ '@babel/preset-env' ],
								plugins: [ '@babel/plugin-transform-react-jsx' ]
							}
						}
					}
				]
			}
		}, webpackBundle ) )
		.pipe( $.rename( function ( path ) {
			if ( tmp[ path.basename ] ) {
				path.dirname = tmp[ path.basename ];
			} else if ( '.map' === path.extname && tmp[ path.basename.replace( /\.js$/, '' ) ] ) {
				path.dirname = tmp[ path.basename.replace( /\.js$/, '' ) ];
			}
			return path;
		} ) )
		.pipe( gulp.dest( './assets/js' ) );
} );

// Image min
gulp.task( 'imagemin', function () {
	return gulp.src( './src/img/**/*' )
		.pipe( $.imagemin( [
			pngquant( {
				quality: '65-80',
				speed  : 1,
				floyd  : 0
			} ),
			mozjpeg( {
				quality    : 85,
				progressive: true
			} ),
			$.imagemin.svgo(),
			$.imagemin.optipng(),
			$.imagemin.gifsicle()
		] ) )
		.pipe( gulp.dest( './assets/img' ) );
} );

// watch
gulp.task( 'watch', function () {
	// Make SASS
	gulp.watch( 'src/scss/**/*.scss', gulp.parallel( 'scss', 'stylelint' ) );
	// JS bundle
	gulp.watch( [ 'src/js/**/*.jsx', 'src/js/**/*.js' ] , gulp.parallel( 'js:bundle', 'eslint' ) );
	// Minify Image
	gulp.watch( 'src/img/**/*', gulp.task( 'imagemin' ) );
} );

// Default Tasks
gulp.task( 'default', gulp.task( 'watch' ) );

// Build
gulp.task( 'build', gulp.parallel( 'js:bundle', 'scss', 'imagemin' ) );

// Lint tasks.
gulp.task( 'lint', gulp.parallel( 'stylelint', 'eslint' ) );
