define(function(require, exports, module) {

var Notify = require('common/bootstrap-notify');

    exports.run = function(options) {
        var $form = $('#coupon-setting-form');

      $('.js-submit').on('click', '', function() {
        let enabled = $('input:radio[name="enabled"]:checked').val()
            if ( enabled < 1 && !confirm(Translator.trans('admin.coupon.setting.confirm_message'))) return false;
            $.post($form.attr('action'), $form.serialize(), function(response){
              window.location.reload();
              Notify.success(Translator.trans('admin.coupon.setting.success_message'));

            }, 'json');
        });

    };

});
