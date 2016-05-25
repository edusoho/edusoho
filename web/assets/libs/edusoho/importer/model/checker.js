define(function (require, exports, module) {
    var Backbone = require('backbone');

    var Checker = Backbone.Model.extend({
        url: '/excel/checker/',
        defaults: {
            "rule":  "ignore"
        },
        initialize : function() {

        }
    });

    module.exports = Checker;
});
