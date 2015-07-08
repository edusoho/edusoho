cordova.define("com.edusoho.kuozhi.v3.plugin.MenuClickPlugin", function(require, exports, module) {
    var exec = require('cordova/exec');
    module.exports = {
        showImages : function(index, images) {
            exec(null, null, "ESNativeCore", "showImages", [index, images]);
        },
        createMenu : function(menuJson) {
            exec(null, null, "ESNativeCore", "createMenu", [menuJson]);
        },
        version : function(version) {
            exec(null, null, "ESNativeCore", "version", [version]);
        },
        openDrawer : function(data){
            exec(null, null, "ESNativeCore", "openDrawer", [data]);
        },
        openWebView : function(data){
            exec(null, null, "ESNativeCore", "openWebView", [data]);
        },
        openCourseChat : function(data){
            exec(null, null, "ESNativeCore", "Course", [data]);
        },
        getUserToken : function($q){
            var deferred = $q.defer(); 
            exec(function(data) {
                deferred.resolve(data);
            }, null, "ESNativeCore", "getUserToken", []);

            return deferred.promise;
        },
        closeWebView : function(data){
            exec(null, null, "ESNativeCore", "closeWebView", [data]);
        },
        saveUserToken : function(user, token){
            exec(null, null, "ESNativeCore", "saveUserToken", [user, token]);
        },
        share : function(url, title, about, pic){
            exec(null, null, "ESNativeCore", "share", [url, title, about, pic]);
        },
        payCourse : function(title, url){
            exec(null, null, "ESNativeCore", "pay", [title, url]);
        },
        learnCourseLesson : function(courseId, lessonId){
            exec(null, null, "ESNativeCore", "learnCourseLesson", [courseId, lessonId]);
        },
        clearUserToken : function(){
            exec(null, null, "ESNativeCore", "clearUserToken", []);
        },
        showDownLesson : function(courseId) {
            exec(null, null, "ESNativeCore", "showDownLesson", [courseId]);
        },
        backWebView : function() {
            exec(null, null, "ESNativeCore", "backWebView", []);
        },
        openPlatformLogin : function(type) {
            exec(null, null, "ESNativeCore", "openPlatformLogin", [type]);
        },
        showKeyInput : function() {
            exec(null, null, "ESNativeCore", "showKeyInput", []);
        },
        post : function() {
            exec(null, null, "ESNativeCore", "openPlatformLogin", [type]);
        }
    };
});
