let $form = $('#register-form');
let validator = $form.validate({
  rules: {
    emailOrMobile: {
      required: true,
      es_remote: {
        type: 'get',
      }
    },
    nickname: {
      required: true,
      minlength: 4,
      maxlength: 18,
      nickname: true,
      chinese_alphanumeric: true,
      es_remote: {
        type: 'get',
      }
    },
    password: {
      minlength: 5,
      maxlength: 20,
    },
    userterms: {
      required: true,
    }
  },
  messages: {
    emailOrMobile: {
      required : Translator.trans('请输入手机/邮箱')
    }
  }
});

$("#register_emailOrMobile").blur(function () {
  var emailOrMobile = $("#register_emailOrMobile").val();
  emSmsCodeValidate(emailOrMobile,$form);
});

$("#register_mobile").blur(function () {
  var mobile = $("#register_mobile").val();
  emSmsCodeValidate(mobile,$form);
});

initDate();
// initGetCodeNum($form);
// initEmail();
// initVerifiedMobile();
// initEmailOrMobile();
// initInvitecode();

function initDate() {
  $(".date").datetimepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    minView: 'month'
  });
}

function initGetCodeNum($form) {
  let $getCodeNum = $('#getcode_num');
  if ($getCodeNum.length > 0) {
    $getCodeNum.click(function () {
      $(this).attr("src", $getCodeNum.data("url") + "?" + Math.random());
    });
    $('[name="captcha_code"]').rules('add', {
      required: true,
      alphanumeric: true,
      es_remote: {
        type: 'get'
      }
    })
    $form.on('focusout.validate', () => {
      if (!$form.validate().element('[name="captcha_code"]')) {
        $getCodeNum.attr("src", $getCodeNum.data("url") + "?" + Math.random());
      }
    })
  }
}

function initEmail() {
  let $email = $('input[name="email"]');
  if ($email.length > 0) {
    $email.rules('add', {
      required: true,
      email: true,
      es_remote: {
        type: 'get'
      }
    })
  }
}

function initVerifiedMobile() {
  let $verifiedMobile = $('input[name="verifiedMobile"]');
  if ($verifiedMobile.length > 0) {
    $('.email_mobile_msg').removeClass('hidden');
    $verifiedMobile.rules('add', {
      required: true,
      phone: true,
      es_remote: {
        type: 'get'
      }
    })
    $form.on('focusout.validate', () => {
      if (!$form.validate().element('input[name="verifiedMobile"]')) {
        $('.js-sms-send').addClass('disabled');
      } else {
        $('.js-sms-send').removeClass('disabled');
      }
    })
  }
}

function initEmailOrMobile() {
  let $emailOrMobile = $('input[name="emailOrMobile"]');
  if ($emailOrMobile.length > 0) {
    $emailOrMobile.rules('add', {
      required: true,
      email_or_mobile_check: true,
      es_remote: true,
    });
    $form.on('focusout.validate', () => {
      if (!$form.validate().element('input[name="verifiedMobile"]')) {
        $('.js-sms-send').addClass('disabled');
      } else {
        $('.js-sms-send').removeClass('disabled');
      }
    });
  }
}

function initInvitecode() {
  let $invitecode = $('.invitecode');
  if ($invitecode.length > 0) {
    $invitecode.rules('add', {
      required: false,
      reg_inviteCode: true,
      es_remote: {
        type: 'get'
      }
    })
  }
}

function emSmsCodeValidate(mobile, $form) {
  var reg_mobile = /^1\d{10}$/;
  var isMobile = reg_mobile.test(mobile);
  if (isMobile) {
    // $('[name="sms_code"]').rules('add', {
    //   required: true,
    //   integer: true,
    //   rangelength: [5, 7],
    //   es_remote: true,
    // })
    $('[name="captcha_code"]').rules('remove');

    $form.on('click', '.js-sms-send', function (e) {
    //   var $mobile_target = validator.query('[name="verifiedMobile"]') == null ? validator.query('[name="emailOrMobile"]') : validator.query('[name="verifiedMobile"]');
    //   $mobile_target.execute(function (error, results, element) {
    //     if (error) {
    //       return;
    //     }
    //   });
    })

  } else {
    $([name = "captcha_code"]).rules('add', {
      required: true,
      alphanumeric: true,
      es_remote: {
        type: 'get'
      }
    })
    $('[name="sms_code"]').rules('remove');
    // validator.addItem({
    //   element: '[name="captcha_code"]',
    //   required: true,
    //   rule: 'alphanumeric remote',
    //   onItemValidated: function (error, message, eleme) {
    //     if (message == Translator.trans('验证码错误')) {
    //       $("#getcode_num").attr("src", $("#getcode_num").data("url") + "?" + Math.random());
    //     }
    //   }
    // });

    // validator.removeItem('[name="sms_code"]');
  }
}

$.validator.addMethod("email_or_mobile_check", function (value, element, params) {
  var emailOrMobile = options.element.val();
  var reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  var reg_mobile = /^1\d{10}$/;
  var result = false;
  var isEmail = reg_email.test(emailOrMobile);
  var isMobile = reg_mobile.test(emailOrMobile);
  if (isMobile) {
    $(".email_mobile_msg").removeClass('hidden');
    $('.js-captcha').addClass('hidden');
    $('.js-sms-send').removeClass('disabled');
  } else {
    $(".email_mobile_msg").addClass('hidden');
    $('.js-sms-send').addClass('disabled');
    $('.js-captcha').removeClass('hidden');
  }
  if (isEmail || isMobile) {
    result = true;
  }
  return this.optional(element) || result;
}, Translator.trans('不允许以1开头的11位纯数字'));