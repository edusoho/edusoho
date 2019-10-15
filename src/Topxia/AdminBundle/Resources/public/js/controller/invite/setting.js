define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
      let $form = $('#invite-form');
      let validator = new Validator({
        element: $form,
        failSilently: true,
        triggerType: 'change',
        autoSubmit: false,
        onFormValidated: function(error, results, $form) {
          if (error) {
            return ;
          }

          if ($('[name=promoted_user_enable]').prop("checked") && $form.find('.table-promoted').length == 0) {
            Notify.danger(Translator.trans('admin.setting.invite.coupon.empty.tips'));
            return;
          }

          if ($('[name=promote_user_enable]').prop("checked") && $form.find('.table-promote').length == 0) {
            Notify.danger(Translator.trans('admin.setting.invite.coupon.empty.tips'));
            return;
          }

          $.post($form.attr('action'), $form.serialize(), function(data){
            if(data.status==false){
              Notify.danger(Translator.trans(data.message));
                return;
            }
            Notify.success(Translator.trans('site.save_success_hint'));
             window.location.reload();
          });

        }
      });

      validator.addItem({
        element: '[name=remain_number]',
        required: false,
        rule: 'positive_integer min{min:1} max{max:1000}',
        errormessage: '请输入1-1000的数字',
      });

      validator.addItem({
        element: '[name=mobile]',
        required: false,
        rule: 'mobile',
        errormessageMobile:'请输入有效的手机号码'
      });

      $('[name=promoted_user_enable]').change(function(e) {
        if ($('[name=promoted_user_enable]').prop("checked")) {
          $('[name=promoted_user_enable]').val(1);
          $('.js-promoted-user-content').removeClass('hidden');
        } else {
          $('[name=promoted_user_enable]').val(0);
          $('.js-promoted-user-content').addClass('hidden');
        }
      });

      $('[name=promote_user_enable]').change(function(e) {
        if ($('[name=promote_user_enable]').prop("checked")) {
          $('[name=promote_user_enable]').val(1);
          $('.js-promote-user-content').removeClass('hidden');
        } else {
          $('[name=promote_user_enable]').val(0);
          $('.js-promote-user-content').addClass('hidden');
        }
      });

      $('#invite-form').on('click', '.js-remove-item', function(e) {
        $(this).parents('.user-content').find('.js-user-batchId').val('');
        $(this).parents('tbody').html('');
      })
    };

});