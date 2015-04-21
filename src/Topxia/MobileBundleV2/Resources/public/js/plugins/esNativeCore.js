cordova.define("com.edusoho.kuozhi.webview.cordova.ESNativeCore", function(require, exports, module) {
    var exec = require('cordova/exec');
    module.exports = {
        showImages : function(index, images) {
            exec(null, null, "esNativeCore", "showImages", [index, images]);
        },
        createMenu : function(menuJson) {
            exec(null, null, "esNativeCore", "createMenu", [menuJson]);
        },
        version : function(version) {
            exec(null, null, "esNativeCore", "version", [version]);
        }
    };
});