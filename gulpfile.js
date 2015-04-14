// Global Variables
var $ = require('gulp-load-plugins')();
var del = require('del')
var gulp = require('gulp');
var bower = require('main-bower-files');

// Config Vars
var config = {
	src: './src/',
	assets: './assets/',
	package: './package.json',
	bower: './bower.json',
};

// Path Vars
var paths = {
	bower: './bower_components/',
	css: {
		src: config.src + 'css/',
		build: config.assets + 'css/'
	},
	js: {
		src: config.src + 'js/',
		build: config.assets + 'js/'
	},
	fonts: {
		src: config.src + 'fonts/',
		build: config.assets + 'fonts/'
	},
	img: {
		src: config.src + 'img/',
		build: config.assets + 'img/'
	}
}

// Filter Vars
// * img excludes .ico because it cannot be compressed; .ico files are automatically added after compression
var filters = {
	'css': $.filter(['*.css']),
	'js': $.filter(['*.js', '!**/jquery.js']),
	'fonts': $.filter(['*.eot', '*.woff', '*.svg', '*.ttf', '*.otf']),
	'img': $.filter(['*.jpg', '*.png', '*.gif']),
};

// Error Handling
var onError = function( err ) {
	console.log( 'Error: ', err.message );
	this. emit( 'end' );
}

gulp.task('default', ['build']);

// Watch
gulp.task('watch', function() {
	gulp.watch(paths.css.src + '**/*', ['css'])
	gulp.watch(paths.js.src + '**/*', ['js'])
	gulp.watch(paths.fonts.src + '**/*', ['fonts'])
	gulp.watch(paths.img.src + '**/*', ['img'])
	gulp.watch('bower.json', ['build'])
});

gulp.task('build', ['css', 'js', 'fonts', 'img'], function() {
	return $.plumber({errorHandler: onError});
});

// # css
// `gulp css` - compiles and minify all sass and css from `src/css` to `assets/css`
gulp.task('css', function() {
	del(paths.css.build + '*')
	return gulp.src(paths.css.src + '**/*.scss')
		.pipe($.plumber({errorHandler: onError}))
		.pipe($.sass())
		.pipe($.print())
		.pipe($.pleeease({
			autoprefixer: {
			  browsers: [
			    'last 2 versions', 'ie 8', 'ie 9', 'android 2.3', 'android 4',
			    'opera 12'
			  ]
			}
		}))
		.pipe($.clipEmptyFiles())
		.pipe(gulp.dest(paths.css.build))
});

// # js
// `gulp js` - runs JSHint on all scripts from `src/js` and bower files, and then compiles, combines and minify to `assets/js/main.js`
gulp.task('js', ['jquery'], function() {
	return gulp.src(bower(['!**/jquery.js', '**/*.js']))
		.pipe($.plumber({errorHandler: onError}))
		.pipe($.addSrc(paths.js.src + '**/*.js'))
		.pipe(filters.js)
		.pipe($.jshint())
		.pipe($.print())
		.pipe($.concat('main.js'))
		.pipe($.uglify())
		.pipe($.clipEmptyFiles())
		.pipe(gulp.dest(paths.js.build))
});

// # jquery
// `gulp jquery` - include a copy of the latest jquery
gulp.task('jquery', function() {
	del(paths.js.build + '*')
	return gulp.src(bower('**/jquery.js'))
		.pipe($.plumber({errorHandler: onError}))
		.pipe($.print())
		.pipe($.uglify())
		.pipe(gulp.dest(paths.js.build))
});

// # fonts
// `gulp fonts` - copy all fonts from `src/fonts` and bower files to `assets/fonts`
gulp.task('fonts', function() {
	del(paths.fonts.build + '*')
	return gulp.src(paths.fonts.src + '**/*')
		.pipe($.plumber({errorHandler: onError}))
		.pipe($.addSrc(bower()))
		.pipe(filters.fonts)
		.pipe($.print())
		.pipe($.flatten())
		.pipe(gulp.dest(paths.fonts.build))
});

// # img
// `gulp images` - lossless compression on all images frome `src/img` and bower files and then include .ico files to `assets/img`
gulp.task('img', function () {
	del(paths.img.build + '*')
	return gulp.src(bower())
		.pipe($.plumber({errorHandler: onError}))
		.pipe($.addSrc(paths.img.src + '**/*'))
		.pipe(filters.img)
		.pipe($.print())
		.pipe($.flatten())
		.pipe($.imagemin({ progressive: true, interlaced: true }))
		.pipe($.addSrc(paths.img.src + + '**/*.ico'))
		.pipe($.addSrc(paths.bower + '**/*.ico'))
		.pipe(gulp.dest(paths.img.build))
});	
		
