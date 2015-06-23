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
        closeWebView : function(data){
            console.log("data");
            exec(null, null, "ESNativeCore", "closeWebView", [data]);
        },
        getUserToken : function(data){
            exec(function(winParam){
                console.log(winParam);
            }, null, "ESNativeCore", "getUserToken", [data]);
        },
        saveUserToken : function(data){
            exec(null, null, "ESNativeCore", "saveUserToken", [data]);
        },
        share : function(data){
            exec(null, null, "ESNativeCore", "share", [data]);
        },
        payCourse : function(data){
            exec(null, null, "ESNativeCore", "payCourse", [data]);
        },
        learnCourseLesson : function(data){
            exec(null, null, "ESNativeCore", "learnCourseLesson", [data]);
        }
    };
});
