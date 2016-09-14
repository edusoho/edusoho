var gulp = require('gulp');
var less = require('gulp-less');
var notify = require('gulp-notify');
var plumber = require('gulp-plumber');
gulp.task('less', function(){
    gulp.src(['web/bundles/customweb/less/login.less'])
        .pipe(plumber({ errorHandler: notify.onError('Error: <%= error.message %>')}))
        .pipe(less())
        .pipe(gulp.dest('web/bundles/customweb/css/'));
});


gulp.task('default', function(){
    gulp.watch("web/bundles/customweb/less/**/*.less",['less']);
});