var gulp = require('gulp');
var wpPot = require('gulp-wp-pot');
var sort = require('gulp-sort');
var notify = require("gulp-notify");
var gutil = require('gulp-util');
gulp.task( 'translate', function () {
    return gulp.src( ['**/*.php'])
        .pipe(sort())
        .pipe(wpPot( {
            domain        : 'plugin-donation-lib',
            package       : 'plugin-donation-lib'
        } ))
        .on('error', gutil.log)
        .pipe(gulp.dest('languages/plugin-donation-lib.pot'))
        .pipe( notify( { message: 'TASK: "translate" Completed! 💯', onLast: true } ) );
});