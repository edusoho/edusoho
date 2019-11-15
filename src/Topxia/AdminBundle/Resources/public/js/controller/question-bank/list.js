define(function(require, exports, module) {

  var Notify = require('common/bootstrap-notify');
  require('../widget/category-select').run('article');

  exports.run = function() {
    var $table = $('#bank-table');

    $table.on('click', '.delete-btn', function() {
      var testpaperNum = $(this).data('testpaperNum');
      var questionNum = $(this).data('questionNum');
      if (testpaperNum > 0 || questionNum > 0) {
        Notify.danger(Translator.trans('admin.question_bank.fail_not_delete', {testpaperNum: testpaperNum, questionNum: questionNum}));
        return;
      }

      if (!confirm(Translator.trans('admin.question_bank.delete_hint'))) {
        return;
      }

      $.post($(this).data('url'), function() {
        window.location.reload();
      }).error(function(error) {
        Notify.danger(Translator.trans('site.delete_fail_hint'));
      });
    });
  };
});