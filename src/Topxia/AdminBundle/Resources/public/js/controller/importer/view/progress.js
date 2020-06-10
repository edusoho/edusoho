define(function (require, exports, module) {
    "use strict";
    var Backbone = require('backbone');
    var _ = require('underscore');
    let html = '<div class="modal-dialog">' +
      '<div class="modal-content">' +
      '<div class="modal-header">' +
      '<h4 class="modal-title"><%= Translator.trans(\'importer.batch_import_title\') %></h4>' +
      '</div>' +
      '<div class="modal-body">' +
      '<div id="import-progress" class="package-update-progress">' +
      '<div class="progress progress-striped active">' +
      '<div class="progress-bar progress-bar-success" style="width: 0%"></div>' +
      '</div>' +
      '<div class="text-success progress-text">' +
      '</div>' +
      '</div>' +
      '</div>' +
      '<div class="modal-footer">' +
      '<a data-loading-text="<%= Translator.trans(\'importer.importing_finish_hint\') %>" class="btn btn-primary hidden js-finish-import-btn" href="javascript:;"><%= Translator.trans(\'importer.importing_finished\') %></a>' +
      '<span class="text-danger js-import-progress-text hidden"><%= Translator.trans(\'importer.importing_loading_hint\') %></span>' +
      '</div>' +
      '</div>' +
      '</div>';

    var ProgressView = Backbone.View.extend({
        template: _.template(html),

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
            this.$el.find('.progress-text').text(Translator.trans('importer.imported_data') + model.get('__quantity'));
            this.$el.find('.js-import-progress-text').removeClass('hidden');
        },

        _onComplete: function (model) {
            this.$el.find('.progress-bar').css('width', "100%");
            this.$el.find('a').removeClass('hidden');
            this.$el.find('.progress-text').text(Translator.trans('importer.imported_success_data') + model.get('__quantity'));
            this.$el.find('.js-import-progress-text').addClass('hidden');
        },

        _onError: function (model) {
            this.stopListening(this.model, "change");
            this.$el.find('.progress-bar').css('width', "100%")
                .removeClass('progress-bar-success')
                .addClass('progress-bar-danger')
            ;
            this.$el.find('.progress-text').text(Translator.trans('site.unknow_server_error')).removeClass('text-success').addClass('text-danger');
            this.$el.find('a').removeClass('hidden').text(Translator.trans('importer.import_reselect_btn'));
        },

        _onFinishImport: function (event) {
            window.location.reload();
        }
    });

    module.exports = ProgressView;
});
