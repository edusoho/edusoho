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
        openDrawer : function(action){
            exec(null, null, "ESNativeCore", "openDrawer", [action]);
        },
        openWebView : function(data){
            exec(null, null, "ESNativeCore", "openWebView", [data]);
        },
        openCourseChat : function(data){
            exec(null, null, "ESNativeCore", "Course", [data]);
        }
    };
});
