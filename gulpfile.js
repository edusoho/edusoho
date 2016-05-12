var gulp = require('gulp'),
    less = require('gulp-less'),
    cssmin = require('gulp-minify-css'),
    notify = require('gulp-notify'),
    plumber = require('gulp-plumber');
gulp.task('testLess',function() {
    gulp.src(['web/assets/less/main.less'])
         .pipe(plumber({errorHandler: notify.onError('Error: <%= error.message %>')}))   
         .pipe(less())
         .pipe(cssmin())
         .pipe(gulp.dest('web/assets/css'));
});
gulp.task('testWatch',function(){
    gulp.watch('web/assets/less/**/*.less',['testLess']);
})