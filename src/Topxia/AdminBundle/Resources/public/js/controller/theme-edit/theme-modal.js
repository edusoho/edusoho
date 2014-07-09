define(function(require, exports, module) {
    var Class = require('class');

    var ThemeConfig = Class.create({

        config: {},

        initialize: function(config) {
            this.config = config;
        },

        getAll: function() {
            return this.config;
        },

        get: function(idName) {
            
        },

        set: function(idName, configById) {
            this.config[idName] = configById;
        },

        setAll: function(config) {
            this.config = config;
        }
    });

    var themeConfig = new ThemeConfig([]);

    module.exports = themeConfig;

});