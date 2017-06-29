define(function(require, exports, module) {
    var Widget = require('widget');
    require('jquery-plugin/jquery.treegrid/0.3.0/css/jquery.treegrid.css');
    require('jquery-plugin/jquery.treegrid/0.3.0/js/jquery.treegrid');
    require('jquery-plugin/jquery.treegrid/0.3.0/js/jquery.cookie');

    var TreeGrid = Widget.extend({
        attrs: {
            element: '.tree'
        },

        events: {},

        setup: function() {},

        create: function(options) {
           return  $(this.get('element')).treegrid(options);
        },

        destroy: function() {}
    });

    module.exports = TreeGrid;
});