define(function (require, exports, module) {
    "use strict";
    var Backbone = require('backbone');
    var _ = require('underscore');

    module.exports = Backbone.View.extend({
        template: _.template(require('./../template/progress.html')),

        events: {
            "click .js-finish-import-btn": 'onFinishImport'
        },

        initialize: function () {
            this.$el.html(this.template());
        },

        onProgress: function (model) {
            var progress = model.get('__progress') + '%';
            this.$el.find('.progress-bar-success').css('width', progress);
            if(model.get('__status')){
                this.$el.find('a').removeClass('hidden');
                this.$el.find('.progress-text').text('导入成功');
            }
        },

        onFinishImport: function (event) {
            $('#modal').modal('hide');
            this.remove();
        }
    });
});
