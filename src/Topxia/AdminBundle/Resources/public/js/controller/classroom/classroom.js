define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);

  exports.run = function () {
    var $table = $('#classroom-table');

    $table.on('click', '.close-classroom,.open-classroom,.cancel-recommend-classroom', function () {
      var $trigger = $(this);
      if (!confirm($trigger.attr('title') + Translator.trans('admin.classroom.operation_hint'))) {
        return;
      }
      $.post($(this).data('url'), function (html) {
        Notify.success($trigger.attr('title') + Translator.trans('admin.classroom.operation_success_hint'));
        var $tr = $(html);
        $('#' + $tr.attr('id')).replaceWith($tr);
      }).error(function () {
        Notify.danger($trigger.attr('title') + Translator.trans('admin.classroom.operation_fail_hint'));
      });
    });

    // 确认后,弹出密码确认框;确认后删除
    $table.on('click', '.delete-classroom', function () {
      let msg = 'admin.classroom.delete_hint';
      let status = false;
      let $tr = $(this).parents('tr');
      $.ajax({
        type: 'post',
        url: $tr.data('url'),
        async: false,
        success: function (data) {
          if (data.status) {
            msg = 'admin.classroom.mall_goods_exist.delete_hint';
          }
        },
        error: function (e) {
          status = 'error';
          let res = e.responseJSON.error.message;
          Notify.danger(res);
        }
      });

      if (status === 'error') {
        return;
      }

      if (!confirm(Translator.trans(msg))) {
        return;
      }

      $.post($(this).data('url'), function (data) {
        if (data.code == 0) {
          $tr.remove();
          Notify.success(data.message);
        } else {
          $('#modal').modal('show').html(data);
        }
      }).error(function (e) {
        let res = e.responseJSON.error.message;
        Notify.danger(res);
      });
    });
  };
});