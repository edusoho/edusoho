define(function (require, exports, module) {
    var Backbone = require('backbone');
    var _ = require('underscore');
    var Importer = require('./../model/importer');

    module.exports = Backbone.View.extend({
        template: _.template(require('./../template/success.html')),
        progressTemplate: _.template(require('./../template/progress.html')),

        events: {
            "click #start-import-btn": "onStartImporter"
        },

        initialize: function () {
            this.$el.html(this.template(this.model.toJSON()));
        },

        onStartImporter: function (event) {
            this.model.chunkUpload();
            this.$el.html(this.progressTemplate());
            //this.$el.show()
        }
    });
});
