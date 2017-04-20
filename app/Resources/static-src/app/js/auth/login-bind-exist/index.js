let $form = $('#bind-exist-form');
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

$('[type="submit"]').click(() => {
  if (validator.form()) {
    $form.submit();
  }
})

$.validator.addMethod("email_or_mobile", function (value, element, params) {
  var emailOrMobile = options.element.val();
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
}, Translator.trans('不允许以1开头的11位纯数字'));

