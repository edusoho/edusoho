define(function(require, exports, module) {

  var Validator = require('bootstrap.validator');
  require('ckeditor');
  require('common/validator-rules').inject(Validator);
  var Notify = require('common/bootstrap-notify');
  require('/bundles/topxiaadmin/js/controller/system/common');
  exports.run = function() {

    // group: 'default'
    CKEDITOR.replace('user_terms_body', {
      toolbar: 'Simple',
      filebrowserImageUploadUrl: $('#user_terms_body').data('imageUploadUrl')
    });


    $("input[name=register_protective]").change(function() {

      var type = $('input[name=register_protective]:checked').val();

      $('.register-help').hide();

      $('.' + type).show();

    });
    

    var validator = new Validator({
      element: '#auth-form'
    });

    if ($('input[name="user_name"]').length > 0) {
        validator.addItem({
            element: '[name="user_name"]',
            required: true
        });
    }
    

    $('.model').on('click',function(){

        var old_modle_value = $('.model.btn-primary').data('modle');
        $('.model').removeClass("btn-primary");
        $(this).addClass("btn-primary");
        var modle = $(this).data('modle');

        if (modle == 'mobile' || modle == 'email_or_mobile') {
            if ($('input[name=_cloud_sms]').val() !=1) {
                $('.model').removeClass("btn-primary");
                $('[data-modle="'+old_modle_value+'"]').addClass("btn-primary");
                modle = old_modle_value;

                Notify.danger("请先开启云短信功能！");
            }
        }

        $('[name="register_mode"]').val(modle);
        if (modle == 'email' || modle == 'email_or_mobile') {
            $('.email-content').removeClass('hidden');
        } else {
            $('.email-content').addClass('hidden');
        }

    });


  };


});