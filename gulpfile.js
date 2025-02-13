const project = 'load-html-files'; // Project Name.



import gulp from 'gulp';
import zip from 'gulp-zip';
import del from 'del';
import rename from 'gulp-rename';
import gutil from 'gulp-util';
import dirSync from 'gulp-directory-sync';







import { exec } from 'child_process';


// Task to run composer update --no-dev
gulp.task('composer-update', (done) => {
    exec('composer update --no-dev', (err, stdout, stderr) => {
        if (err) {
            console.error('Error running composer update --no-dev:', stderr);
            done(err);
        } else {
            console.log(stdout);
            done();
        }
    });
});
gulp.task('zip', (done) => {
    gulp.src('dist/**/*')
        .pipe(rename(function (file) {
            file.dirname = project + '/' + file.dirname;
        }))
        .pipe(zip(project + '-free.zip'))
        .pipe(gulp.dest('zipped'))
    done()
});


gulp.task('clean', () => {
    return del([
        'dist/**/sass/',
        'dist/**/*.css.map',
        'dist/composer.*',
        'dist/vendor/bin/',
        'dist/vendor/composer/ca-bundle/',
        'dist/vendor/composer/installers/',
        'dist/vendor/**/.git*',
        'dist/vendor/**/.travis.yml',
        'dist/vendor/**/.codeclimate.yml',
        'dist/vendor/**/composer.json',
        'dist/vendor/**/package.json',
        'dist/vendor/**/gulpfile.js',
        'dist/vendor/**/*.md',
        'dist/vendor/squizlabs',
        'dist/vendor/wp-coding-standards'
    ]);
});


gulp.task('sync', () => {
    return gulp.src('.', {allowEmpty: true})
        .pipe(dirSync('load-html-files', 'dist', {printSummary: true}))
        .on('error', gutil.log);
});

gulp.task('translate', (cb) => {
    exec(' wp i18n make-pot ./load-html-files  ./load-html-files/languages/load-html-files.pot --skip-audit --exclude=\'./vendor\'', (err, stdout, stderr) => {
        console.log(stdout);
        console.log(stderr);
        cb(err);
    });
});


gulp.task('build', gulp.series('composer-update','sync',  'clean', 'translate', 'zip'));



