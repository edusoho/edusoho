cordova.define('cordova/plugin_list', function(require, exports, module) {
    module.exports = [
        {
            "file": "plugins/esNativeCore.js",
            "id": "com.edusoho.kuozhi.webview.cordova.ESNativeCore",
            "merges": [
                "window.ESNativeCore"
            ]
        }
    ]
});