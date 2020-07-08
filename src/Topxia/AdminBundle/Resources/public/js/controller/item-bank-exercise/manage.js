define(function(require, exports, module) {

  require('../widget/category-select').run();
  var Notify = require('common/bootstrap-notify');

  exports.run = function (options) {
    var $table = $('#exercise-table');
    $table.on('click', '.cancel-recommend-exercise', function () {
      $.post($(this).data('url'), function (html) {
        Notify.success(Translator.trans('admin.course.cancel_recommend_success_hint'));
        window.location.reload();
      });
    });

    $table.on('click', '.publish-exercise', function() {
      var studentNum = $(this).closest('tr').next().val();
      if (!confirm(Translator.trans('admin.course.publish_hint'))) return false;
      $.post($(this).data('url'), function(response) {
        Notify.success(Translator.trans('admin.course.publish_success_hint'));
        window.location.reload();
      }).error(function(e) {
        var res = e.responseJSON.error.message || Translator.trans('admin.course.unknow_error_hint');
        Notify.danger(res);
      });
    });

    $table.on('click', '.delete-exercise', function() {
      var $this = $(this);
      if (!confirm(Translator.trans('admin.course.delete_hint')))
        return;
      $.post($this.data('url'), function(data) {
        Notify.success(Translator.trans('site.delete_success_hint'));
        window.location.reload();
      });
    });
  };
});