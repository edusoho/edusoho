var gulp = require('gulp');
var less = require('gulp-less');
var notify = require('gulp-notify');
var plumber = require('gulp-plumber');
var sourcemaps = require('gulp-sourcemaps');

gulp.task('less',function(){
    return gulp.src([
        'less/main.less',
        // 'less/main-blue.less',
        // 'less/main-blue-light.less',
        // 'less/main-en-us.less',
        // 'less/main-green-light.less',
        // 'less/main-orange-light.less',
        // 'less/main-orange.less',
        // 'less/main-purple-light.less',
        // 'less/main-purple.less',
        // 'less/main-red-light.less',
        // 'less/main-red.less',
        ])
        .pipe(plumber({errorHandler: notify.onError('Error: <%= error.message %>')}))
        .pipe(sourcemaps.init())
        .pipe(less())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest('css/'));
});

gulp.task('less:watch',function(){
    gulp.watch('less/**/*.less',['less']);
})

gulp.task('default',['less:watch']);
