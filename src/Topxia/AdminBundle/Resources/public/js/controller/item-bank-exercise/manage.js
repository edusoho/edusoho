define(function(require, exports, module) {

  require('../widget/category-select').run();
  var Notify = require('common/bootstrap-notify');

  exports.run = function (options) {
    var $table = $('#exercise-table');
    $('.cancel-recommend-exercise').on('click', function () {
      $.post($(this).data('url'), function (html) {
        Notify.success(Translator.trans('admin.item_bank_exercise.exercise.cancel_recommend_success_hint'));
        window.location.reload();
      });
    });

    $table.on('click', '.publish-exercise', function() {
      if (!confirm(Translator.trans('admin.item_bank_exercise.exercise.publish_hint'))) return false;
      $.post($(this).data('url'), function(response) {
        Notify.success(Translator.trans('admin.item_bank_exercise.exercise.publish_success_hint'));
        window.location.reload();
      }).error(function(e) {
        var res = e.responseJSON.error.message || Translator.trans('admin.course.unknow_error_hint');
        Notify.danger(res);
      });
    });

    $table.on('click', '.close-exercise', function() {
      if (!confirm(Translator.trans('admin.item_bank_exercise.exercise.close_hint'))) return false;
      $.post($(this).data('url'), function(response) {
        Notify.success(Translator.trans('admin.item_bank_exercise.exercise.close_success_hint'));
        window.location.reload();
      }).error(function(e) {
        var res = e.responseJSON.error.message || Translator.trans('admin.course.unknow_error_hint');
        Notify.danger(res);
      });
    });

    $table.on('click', '.delete-exercise', function() {
      var $this = $(this);
      let msg = 'admin.item_bank_exercise.exercise.delete_hint';
      let status = null;
      let $tr = $this.parents('tr');
      $.ajax({
        type: 'post',
        url: $tr.data('url'),
        async: false,
        success: function (data) {
          status = data.status;
          if (status === 'should_delete_mall_goods') {
            msg = 'admin.item_bank_exercise.exercise.mall_goods_exist.delete_hint';
          }
          if (status === 'cannot_delete') {
            let res = Translator.trans('mall.goods.exist.delete_fail_hint');
            Notify.danger(res);
          }
        }
      });

      if (status === 'cannot_delete') {
        return;
      }

      if (!confirm(Translator.trans(msg)))
        return;
      $.post($this.data('url'), function(data) {
        Notify.success(Translator.trans('site.delete_success_hint'));
        window.location.reload();
      });
    });
  };
});