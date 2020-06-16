define(function (require, exports, module) {
    var Backbone = require('backbone');
    var _ = require('underscore');
    var ProgressView = require('./progress');
    let html = '<div class="page-header clearfix">' +
      '<h1 class="pull-left"><%= Translator.trans(\'site.step_two\') %></h1>' +
      '</div>' +
      '<div class="row">' +
      '<div id="user-import">' +
      '<% $.each(checkInfo, function(index, item){  %>' +
      '<div class="col-md-offset-2"><%= item %></div><br>' +
      '<%}) %>' +
      '<div class="col-md-offset-2"><%= Translator.trans(\'importer.import_verify_tips_start\') %> <b><%= importData.length %></b><%= Translator.trans(\'site.information\') %></div><br>' +
      '<div class="col-md-offset-2">' +
      '<button type="button" class="btn btn-primary" id="start-import-btn"><%= Translator.trans(\'importer.import_confirm_btn\') %></button>\n' +
      '<a type="button" class="btn btn-primary" href="#index"><%= Translator.trans(\'importer.import_back_btn\') %></a>' +
      '</div>' +
      '</div>' +
      '</div>';
    module.exports = Backbone.View.extend({
        template: _.template(html),

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
