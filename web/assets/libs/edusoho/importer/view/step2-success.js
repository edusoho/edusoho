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
            this.progress = new ProgressView();
            $('#modal').html(this.progress.el);
            $('#modal').modal({
                show: true,
                backdrop: 'static',
                keyboard: false
            });
            this.progress.listenTo(this.model, 'change', this.progress.onProgress);
            this.model.chunkUpload();
        },
    });
});
