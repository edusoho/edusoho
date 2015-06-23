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
        saveUserToken : function(data){
            exec(null, null, "ESNativeCore", "saveUserToken", [data]);
        },
        share : function(data){
            exec(null, null, "ESNativeCore", "share", [data]);
        },
        payCourse : function(title, url){
            exec(null, null, "ESNativeCore", "payCourse", [title, url]);
        },
        learnCourseLesson : function(data){
            exec(null, null, "ESNativeCore", "learnCourseLesson", [data]);
        }
    };
});
