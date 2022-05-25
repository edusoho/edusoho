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
      let code = 0;
      $.ajax({
        type: 'post',
        url: $(this).data('check-url'),
        async: false,
        success: function (data) {
          code = data.code;
          if (code === 1) {
            msg = 'admin.question_bank.mall_goods_exist.delete_hint';
          }
        },
        error: function (e) {
          code = 2;
          let res = e.responseJSON.error.message;
          Notify.danger(res);
        }
      });

      if (code === 2) {
        return;
      }
      let msg = 'admin.question_bank.delete_hint';
      $.ajax({
        type: 'post',
        url: $(this).data('check-url'),
        async: false,
        success: function (data) {
          if (data.code === 1) {
            msg = 'admin.question_bank.mall_goods_exist.delete_hint';
          }
        },
        error:function (e){
          let res = e.responseJSON.error.message;
          Notify.danger(res);
        }
      });

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