/*
 js bridge adapter v1.0.0
 author suju
*/
;(function() {

    //default brideg name
    var bridgeName = "cordova";
    var version = "1.0.0";
    var modules = {};
    var require;

    require = function(id) {

        var build = function(module) {
            var factory = module.factory;
            module.exports = {};
            delete module.factory;
            factory(require, module.exports, module);
            return module.exports;
        };  

        if (modules[id].factory) {
            return build(modules[id]);
        }
        return modules[id].exports;
    };

    var define = function(id, factory) {
        this.moduleMap = modules;
        modules[id] = {
            id: id,
            factory: factory
        };
    };

    define(bridgeName + "/exec", function(require, exports, module) {

        var core = require("jsBridgeAdapter");
        var androidExec = function(success, error, targetName, method, args, isAnsy) {
            var callbackId = core.messageLoop.putCallback(success, error);
            var argsJson = JSON.stringify(args);
            if (true == isAnsy) {
                var message = prompt(argsJson, JSON.stringify([targetName, method]));
                var resultJson = JSON.parse(message);
                core.invokeCallback(callbackId, "success", resultJson);
                return;
            }
            //webViewBridgeAdapter webView obj
            webViewBridgeAdapter.exec(callbackId, targetName, method, argsJson);
        };
        module.exports = androidExec;
    });

    define("jsBridgeAdapter/plugin", function(require, exports, module) {
        
        function injectScript(url, onload, onerror) {
            var script = document.createElement("script");
            // onload fires even when script fails loads with an error.
            script.onload = onload;
            script.onerror = onerror || onload;
            script.src = url;
            document.head.appendChild(script);
        }

        function findJsBridgeAdapterPath() {
            var path = null;
            var scripts = document.getElementsByTagName('script');
            var term = bridgeName + '.js';
            for (var n = scripts.length-1; n>-1; n--) {
                var src = scripts[n].src;
                var pos = src.indexOf(term);
                if (pos > 0) {
                    path = src.substring(0, pos);
                    break;
                }
            }
            return path;
        }

        function injectPluginScript(pathPrefix, finishPluginLoading) {
            var pluginPath = pathPrefix + bridgeName + '_plugins.js';
            injectScript(pluginPath, function() {
                try {
                    var moduleList = require(bridgeName + "/plugin_list");
                    handlePluginsObject(pathPrefix, moduleList, finishPluginLoading);
                }
                catch (e) {
                    finishPluginLoading();
                }
            }, finishPluginLoading);
        }

        function mergeModule(name, toMerge) {
            var mergeObjToWindow = function(parentObj) {
                var parent = parentObj, merge;
                for (var i = 1; i < mergeObjs.length - 1; i++) {
                    merge = parent[mergeObjs[i]];
                    if (merge == null || merge == undefined) {
                        merge = parent[mergeObjs[i]] = {};
                    }
                    parent = merge;
                };
                parent[mergeObjs[mergeObjs.length - 1]] = toMergeexprots;
            };

            var toMergeexprots = require(name);
            var mergeObjs = toMerge.split(".") || [];
            if (mergeObjs.length > 0) {
                var firstName = mergeObjs[0];
                if ("window" == firstName) {
                    mergeObjToWindow(window);
                } else {
                    window[firstName] = {};
                    mergeObjToWindow(window[firstName]);
                }
            }
        }

        function onScriptLoadingComplete(moduleList, finishPluginLoading) {
            for (var i = 0, module; module = moduleList[i]; i++) {
                if (module) {
                    try {
                        if (module.merges && module.merges.length) {
                            for (var k = 0; k < module.merges.length; k++) {
                                mergeModule(module.id, module.merges[k]);
                            }
                        }
                    }
                    catch(err) {
                        // error with module, most likely clobbers, should we continue?
                    }
                }
            }

            finishPluginLoading();
        }

        function handlePluginsObject(path, moduleList, finishPluginLoading) {
            // Now inject the scripts.
            var scriptCounter = moduleList.length;

            if (!scriptCounter) {
                finishPluginLoading();
                return;
            }
            function scriptLoadedCallback() {
                if (!--scriptCounter) {
                    onScriptLoadingComplete(moduleList, finishPluginLoading);
                }
            }

            for (var i = 0; i < moduleList.length; i++) {
                injectScript(path + moduleList[i].file, scriptLoadedCallback);
            }
        }

        exports.load = function(callback) {
            var pathPrefix = findJsBridgeAdapterPath();
            if (pathPrefix === null) {
                console.log('Could not find jsBridgeAdapter.js script tag. Plugin loading may fail.');
                pathPrefix = '';
            }
            injectPluginScript(pathPrefix, callback);
        };

    });

    define("jsBridgeAdapter/messageInvoke", function(require, exports, module) {
        var callbackMap = {};

        function UUIDcreatePart(length) {
            var uuidpart = "";
            for (var i=0; i<length; i++) {
                var uuidchar = parseInt((Math.random() * 256), 10).toString(16);
                if (uuidchar.length == 1) {
                    uuidchar = "0" + uuidchar;
                }
                uuidpart += uuidchar;
            }
            return uuidpart;
        }

        var messageLoop = {
            putCallback : function(success, error) {
                var name = this.createUUID();
                callbackMap[name] = {
                    success : success,
                    error : error
                };
                return name;
            },
            getCallback : function(name) {
                return callbackMap[name];
            },
            removeCallback : function(name) {
                delete callbackMap[name];
            },
            createUUID : function() {
                return UUIDcreatePart(4) + '-' +
                    UUIDcreatePart(2) + '-' +
                    UUIDcreatePart(2) + '-' +
                    UUIDcreatePart(2) + '-' +
                    UUIDcreatePart(6);
            }
        };

        module.exports = messageLoop;
    });

    define("jsBridgeAdapter", function(require, exports, module) {
        var messageLoop = require("jsBridgeAdapter/messageInvoke");
        var deviceready = false;

        var jsBridgeAdapter = {
            define : define,
            require : require,
            version : version,
            messageLoop : messageLoop,
            invokeCallback : function(callbackName, callbackType, args) {
                console.log("invokeCallback:" + callbackType);
                var callback = this.messageLoop.getCallback(callbackName);
                if (callback.success || callbac.error) {
                    callback[callbackType](args);
                    this.messageLoop.removeCallback(callbackName);
                }
            },
            isDeviceready : function() {
                return deviceready;
            },
            setDeviceready : function(isReadly) {
                deviceready = isReadly;
            }
        };

        module.exports = jsBridgeAdapter;
    });

    define("jsBridgeAdapter/platform", function(require, exports, module) {

        function deviceready() {
            require("jsBridgeAdapter").setDeviceready(true);
            var event = document.createEvent('HTMLEvents');
            event.initEvent("deviceready", true, true);
            event.eventType = 'message';
            document.dispatchEvent(event);
        }

        module.exports = {
            id: 'android',
            bootstrap: function() {
                console.log("platform:bootstrap");
                var exec = require("cordova/exec");
                var callback = function() {
                    deviceready();
                };
                exec(callback, null, "App", "startup", []);
            }
        };
    });

    define("jsBridgeAdapter/init", function(require, exports, module) {
        module.exports = require("jsBridgeAdapter");
        require("jsBridgeAdapter/plugin").load(function() {
            var platform = require("jsBridgeAdapter/platform");
            platform.bootstrap();
        });
        
    });

    window.cordova = window.jsBridgeAdapter = require("jsBridgeAdapter/init");

})();