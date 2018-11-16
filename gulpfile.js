var gulp   = require('gulp'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    rename = require('gulp-rename');

gulp.task('default', function () {
    var path = './src/asset/js/';

    return gulp.src([
        path + 'jquery.multipleField.js'
    ])
        .pipe(concat('jquery.multipleField.js'))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest(path));
});
