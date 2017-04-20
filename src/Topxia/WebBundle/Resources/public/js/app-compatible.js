define(function(require, exports, module) {
    exports.load = function(name) {
        if (window.app.jsPaths[name.split('/', 1)[0]] == undefined) {
            name = window.app.basePath + '/bundles/topxiaweb/js/controller/' + name;
        }
        seajs.use(name, function(module) {
            if ($.isFunction(module.run)) {
                module.run();
            }
        });
    };

    exports.loadScript = function(scripts) {
        for (var index in scripts) {
            exports.load(scripts[index]);
        }
    }

    window.app.load = exports.load;
    
    if (app.themeGlobalScript) {
        exports.load(app.themeGlobalScript);
    }

    if (app.controller) {
        exports.load(app.controller);
    }

    if (app.scripts) {
        exports.loadScript(app.scripts);
    }
});
