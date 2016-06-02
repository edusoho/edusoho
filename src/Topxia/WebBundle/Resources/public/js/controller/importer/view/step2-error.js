define(function (require, exports, module) {
    var Backbone = require('backbone');
    var _ = require('underscore');

    module.exports = Backbone.View.extend({
        template: _.template(require('./../template/error.html')),

        initialize: function (errors) {
            this.$el.html(this.template({errors: errors}));
        }
    });
});
