define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    exports.run = function() {
        var editForm = Widget.extend({
            events: {
                    'click .js-add-collapse' : 'onAddBtn',
                    'change .lesson-content input' : 'onChangeUpdateBtn'
            },

            setup: function() {
                // initialize code
            },
            onAddBtn: function() {
                
            },
            onChangeUpdateBtn: function() {

            }
        });
    };

});