define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
      let validator = new Validator({
        element: '#invite-form',
        failSilently: true,
        triggerType: 'change'
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