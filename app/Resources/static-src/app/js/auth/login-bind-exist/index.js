import notify from 'common/notify';

let $form = $('#bind-exist-form');
let $btn = $form.find('#set-bind-exist-btn');
let validator = $form.validate({
  rules: {
    emailOrMobile: {
      required: true,
      email_or_mobile: true,
    },
    password: {
      required: true,
    }
  }
});

$btn.click(() => {
  if (validator.form()) {
    $btn.button('loading');
    $("#bind-exist-form-error").hide();
    $.post($form.attr('action'), $form.serialize(), function (response) {

      console.log(response);
      if (!response.success) {
        $("#bind-exist-form-error").html(response.message).show();
         $btn.button('reset');
        return;
      }
      notify('success',Translator.trans('绑定帐号成功，正在跳转至首页！'));
      window.location.href = response._target_path;
    }, 'json').fail(function () {
      notify('danger',Translator.trans('绑定失败，帐号或密码错误。'));
    }).always(function () {
      $btn.button('reset');
    });
  }
})

$.validator.addMethod("email_or_mobile", function (value, element, params) {
  var emailOrMobile = value;
  var reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  var reg_mobile = /^1\d{10}$/;
  var result = false;
  var isEmail = reg_email.test(emailOrMobile);
  var isMobile = reg_mobile.test(emailOrMobile);
  if (isMobile) {
    $(".email_mobile_msg").removeClass('hidden');
  } else {
    $(".email_mobile_msg").addClass('hidden');
  }
  if (isEmail || isMobile) {
    result = true;
  }
  return this.optional(element) || result;
}, Translator.trans('请输入正确格式的Email/手机'));

