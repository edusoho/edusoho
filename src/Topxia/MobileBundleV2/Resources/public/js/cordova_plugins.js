cordova.define('cordova/plugin_list', function(require, exports, module) {
    module.exports = [
        {
            "file": "plugins/cordovaUtil.js",
            "id": "com.edusoho.kuozhi.ui.htmlView.plugin.CordovaUtil",
            "merges": [
                "navigator.cordovaUtil"
            ]
        }
    ]
});