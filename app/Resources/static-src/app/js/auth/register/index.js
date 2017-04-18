// var Validator = require('bootstrap.validator');
// require('common/validator-rules').inject(Validator);
// require("jquery.bootstrap-datetimepicker");
// var SmsSender = require('../widget/sms-sender');
// //var CaptchaModal = require('./captcha-mobile-modal.js');


var $form = $('#register-form');
let validator = initValidator();
getCodeNum(validator);









function initValidator($form) {
  return $form.validate({
    rules: {
      nickname: {
        required: true,
        min: 4,
        max: 18,
        nickname: true,
        remote: {
          url: $('#register_nickname').data('url'),
          type: 'get',
          data: {
            'value': function () {
              return $('#register_nickname').val();
            }
          }
        }
      }
    },
    password: {
      required: true,
      min: 4,
      max: 20,
    },

  })
}

function getCodeNum(validator) {
  if ($("#getcode_num").length > 0) {

    $("#getcode_num").click(function () {
      $(this).attr("src", $("#getcode_num").data("url") + "?" + Math.random());
    });



    validator.addItem({
      element: '[name="captcha_code"]',
      required: true,
      rule: 'alphanumeric remote',
      onItemValidated: function (error, message, eleme) {
        if (message == Translator.trans('验证码错误')) {
          $("#getcode_num").attr("src", $("#getcode_num").data("url") + "?" + Math.random());
        }
      }
    });
  };
}





$('#register-btn').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loadding');
    $form.submit();
  }
})

$(".date").datetimepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  minView: 'month'
});
