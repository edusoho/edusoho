cordova.define("com.edusoho.kuozhi.ui.htmlView.plugin.CordovaUtil", function(require, exports, module) {
    var exec = require('cordova/exec');
    module.exports = {
        showImages : function(index, images) {
            exec(null, null, "CordovaUtil", "showImages", [index, images]);
        },
        createMenu : function(menuJson) {
            exec(null, null, "CordovaUtil", "createMenu", [menuJson]);
        }
    };
});