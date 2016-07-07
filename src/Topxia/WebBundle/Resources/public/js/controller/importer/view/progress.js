define(function (require, exports, module) {
    "use strict";
    var Backbone = require('backbone');
    var _ = require('underscore');

    var ProgressView = Backbone.View.extend({
        template: _.template(require('./../template/progress.html')),

        events: {
            "click .js-finish-import-btn": '_onFinishImport'
        },

        initialize: function () {
            this.$el.html(this.template());
            this.listenTo(this.model, 'change', this._onChange);
        },

        _capitalize: function (str) {
            return str.charAt(0).toUpperCase() + str.substr(1);
        },

        _onChange: function (model) {
            var on = "_on" + this._capitalize(model.get('__status'));
            if(ProgressView.prototype.hasOwnProperty(on)){
                this[on](model);
            }
        },

        _onProgress: function (model) {
            var progress = model.get('__progress') + '%';
            this.$el.find('.progress-bar-success').css('width', progress);
            this.$el.find('.progress-text').text('已经导入: ' + model.get('__quantity'));
            this.$el.find('.js-import-progress-text').removeClass('hidden');
        },

        _onComplete: function (model) {
            this.$el.find('.progress-bar').css('width', "100%");
            this.$el.find('a').removeClass('hidden');
            this.$el.find('.progress-text').text('导入成功, 总共导入: ' + model.get('__quantity'));
            this.$el.find('.js-import-progress-text').addClass('hidden');
        },

        _onError: function (model) {
            this.stopListening(this.model, "change");
            this.$el.find('.progress-bar').css('width', "100%")
                .removeClass('progress-bar-success')
                .addClass('progress-bar-danger')
            ;
            this.$el.find('.progress-text').text('发生未知错误').removeClass('text-success').addClass('text-danger');
            this.$el.find('a').removeClass('hidden').text('重新导入');
        },

        _onFinishImport: function (event) {
            window.location.reload();
        }
    });

    module.exports = ProgressView;
});
