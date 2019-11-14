define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  var Notify = require('common/bootstrap-notify');
  require('common/validator-rules').inject(Validator);
    
  exports.run = function() {
    var $table = $('#category-table');

    $table.on('click', '.delete-btn', function() {
      if (!confirm(Translator.trans('admin.category.delete_hint'))) {
        return;
      }
      $.post($(this).data('url'), function() {
        window.location.reload();
      }).error(function(error) {
        Notify.danger(error.responseJSON.error.message);
      });
    });
  };

});