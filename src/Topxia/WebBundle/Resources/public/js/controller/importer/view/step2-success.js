define(function (require, exports, module) {
    var Backbone = require('backbone');
    var _ = require('underscore');
    var ProgressView = require('./progress');
    module.exports = Backbone.View.extend({
        template: _.template(require('./../template/success.html')),


        events: {
            "click #start-import-btn": "onStartImport",
        },

        initialize: function () {
            this.$el.html(this.template(this.model.toJSON()));
        },

        onStartImport: function (event) {
            var self = this;
            this.progress = new ProgressView({
                model: this.model
            });

            var $modal = $('#modal');
            $modal.html(this.progress.el);
            $modal.modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
            self.model.chunkImport();
        }
    });
});
