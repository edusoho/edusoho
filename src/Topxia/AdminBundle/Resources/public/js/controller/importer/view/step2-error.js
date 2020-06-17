define(function (require, exports, module) {
  var Backbone = require('backbone');
  var _ = require('underscore');
  let html = '<div class="page-header clearfix">' +
    '<h1 class="pull-left"><%= Translator.trans(\'site.step_two\') %></h1>' +
    '</div>' +
    '<div class="row">' +
    '<div id="user-import">' +
    '<% $.each(errors, function(index, error){ %>' +
    '<div class="col-md-offset-2"><%= error %></div>' +
    '<% }); %>' +
    '<br>' +
    '<div class="col-md-offset-2"><a type="button" class="btn btn-primary" href="#index"><%= Translator.trans(\'importer.import_reselect_btn\') %></a></div>' +
    '</div>' +
    '</div>';

  module.exports = Backbone.View.extend({
    template: _.template(html),

    initialize: function (errors) {
      this.$el.html(this.template({errors: errors}));
    }
  });
});
