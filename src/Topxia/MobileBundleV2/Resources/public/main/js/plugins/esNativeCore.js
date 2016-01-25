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
            }, null, "ESNativeCore", "getUserToken", [], true);

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
        pay : function(title, url){
            exec(null, null, "ESNativeCore", "pay", [title, url]);
        },
        learnCourseLesson : function(courseId, lessonId, lessonArray){
            /*
                *lessonArray [];
            */
            exec(null, null, "ESNativeCore", "learnCourseLesson", [courseId, lessonId, lessonArray]);
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
        post : function($q, url, headers, params) {
            var deferred = $q.defer(); 
            exec(function(data) {
                deferred.resolve(data);
            }, function(error) {
                deferred.reject(error);
            }, "ESNativeCore", "post", [ url, headers, params ]);

            return deferred.promise;
        },
        showInput : function(title, content, type, successCallback) {
            exec(function(input) {
                if (successCallback) {
                    successCallback(input);
                }
                
            }, null, "ESNativeCore", "showInput", [ title, content, type ]);
        },
        startAppView : function(name, data) {

            var type = arguments[2];
            if(!arguments[2]) {
                type = "Activity";
            }
            exec(null, null, "ESNativeCore", "startAppView", [ name, data, type ]);
        },
        updateUser : function(user){
            exec(null, null, "ESNativeCore", "updateUser", [ user ]);
        },
        uploadImage : function($q, url, headers, params, acceptType) {
            var deferred = $q.defer(); 
            exec(function(data) {
                deferred.resolve(data);
            }, null, "ESNativeCore", "uploadImage", [ url, headers, params, acceptType ]);

            return deferred.promise;
        },
        redirect : function(body) {
            exec(null, null, "ESNativeCore", "redirect", [ body ]);
        },
        getThirdConfig : function ($q) {
            var deferred = $q.defer(); 
            exec(function(data) {
                deferred.resolve(data);
            }, null, "ESNativeCore", "getThirdConfig", []);

            return deferred.promise;
        },
        sendNativeMessage : function(type, data) {
            exec(null, null, "ESNativeCore", "sendNativeMessage", [ type, data ]);
        }
    };
});
