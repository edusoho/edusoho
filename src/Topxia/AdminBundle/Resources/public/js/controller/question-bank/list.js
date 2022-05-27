define(function (require, exports, module) {

  let Notify = require('common/bootstrap-notify');
  require('../widget/category-select').run();

  exports.run = function () {
    let $table = $('#bank-table');

    $table.on('click', '.delete-btn', function () {
      let testpaperNum = $(this).data('testpaperNum');
      let questionNum = $(this).data('questionNum');
      if (testpaperNum > 0 || questionNum > 0) {
        Notify.danger(Translator.trans('admin.question_bank.fail_not_delete', {
          testpaperNum: testpaperNum,
          questionNum: questionNum
        }));
        return;
      }
      let msg = 'admin.question_bank.delete_hint';
      let status = false;
      $.ajax({
        type: 'post',
        url: $(this).data('check-url'),
        async: false,
        success: function (data) {
          if (data.status) {
            msg = 'admin.question_bank.mall_goods_exist.delete_hint';
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

      $.post($(this).data('url'), function () {
        window.location.reload();
      }).error(function (error) {
        Notify.danger(Translator.trans('site.delete_fail_hint'));
      });
    });
  };
});