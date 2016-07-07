define(function (require, exports, module) {
    var Backbone = require('backbone');

    var Checker = Backbone.Model.extend({
        defaults: {
            "rule":  "ignore",
        }
    });

    module.exports = Checker;
});
