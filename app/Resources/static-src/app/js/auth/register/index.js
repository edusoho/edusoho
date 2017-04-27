let validator = $('#register-form').validate({
  rules: {
    nickname: {
      required: true,
      byte_minlength: 4,
      byte_maxlength: 18,
      nickname: true,
      chinese_alphanumeric: true,
      es_remote: {
        type: 'get',
      }
    },
    password: {
      minlength: 5,
      maxlength: 20,
    }
  },
});

$("#register_emailOrMobile").blur(function () {
  let emailOrMobile = $("#register_emailOrMobile").val();
  emSmsCodeValidate(emailOrMobile);
});

$("#register_mobile").blur(function () {
  let mobile = $("#register_mobile").val();
  emSmsCodeValidate(mobile);
});

initDate();
initCaptchaCode();
initRegisterTypeRule();
initInviteCodeRule();
intiUserTermsRule();

function initDate() {
  $(".date").datetimepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    minView: 'month'
  });
}

function initCaptchaCode() {
  let $getCodeNum = $('#getcode_num');
  if ($getCodeNum.length > 0) {
    $getCodeNum.click(function () {
      $(this).attr("src", $getCodeNum.data("url") + "?" + Math.random());
    });
    initCaptchaCodeRule();
  }
}

function initRegisterTypeRule() {
  let $email = $('input[name="email"]');
  if ($email.length > 0) {
    $email.rules('add', {
      required: true,
      email: true,
      es_remote: {
        type: 'get'
      },
      messages: {
        required: Translator.trans('请输入邮箱'),
      }
    })
  }

  let $emailOrMobile = $('input[name="emailOrMobile"]');
  if ($emailOrMobile.length > 0) {
    $emailOrMobile.rules('add', {
      required: true,
      email_or_mobile_check: true,
      es_remote: {
        type: 'get',
        callback: function (bool) {
          if (bool) {
            $('.js-sms-send').removeClass('disabled');
          } else {
            $('.js-sms-send').addClass('disabled');
          }
        }
      },
      messages: {
        required: Translator.trans('请输入手机/邮箱')
      },
    });
  }

  let $verifiedMobile = $('input[name="verifiedMobile"]');
  if ($verifiedMobile.length > 0) {
    $('.email_mobile_msg').removeClass('hidden');
    $verifiedMobile.rules('add', {
      required: true,
      phone: true,
      es_remote: {
        type: 'get',
        callback: function (bool) {
          if (bool) {
            $('.js-sms-send').removeClass('disabled');
          } else {
            $('.js-sms-send').addClass('disabled');
          }
        }
      },
      messages: {
        required: Translator.trans('请输入手机')
      },
    })
  }
}

function initInviteCodeRule() {
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

function intiUserTermsRule() {
  if ($('#user_terms').length) {
    $('#user_terms').rules('add', {
      required: true,
      messages: {
        required: Translator.trans('勾选同意此服务协议，才能继续注册')
      }
    })
  }
}

function initCaptchaCodeRule() {
  $('[name="captcha_code"]').rules('add', {
    required: true,
    alphanumeric: true,
    es_remote: {
      type: 'get',
      callback: function (bool) {
        if (!bool) {
          $('#getcode_num').attr("src", $('#getcode_num').data("url") + "?" + Math.random());
        }
      }
    },
  })
}

function initSmsCodeRule() {
  $('[name="sms_code"]').rules('add', {
    required: true,
    integer: true,
    rangelength: [6, 6],
    es_remote: true,
    messages: {
      rangelength: Translator.trans('请输入6位验证码')
    }
  })
}

function emSmsCodeValidate(mobile) {
  let reg_mobile = /^1\d{10}$/;
  let isMobile = reg_mobile.test(mobile);
  if (isMobile) {
    initSmsCodeRule();
    $('[name="captcha_code"]').rules('remove');
  } else {
    initCaptchaCodeRule();
    $('[name="sms_code"]').rules('remove');
  }
}

$.validator.addMethod("email_or_mobile_check", function (value, element, params) {
  let reg_email = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  var reg_mobile = /^1\d{10}$/;
  var result = false;
  var isEmail = reg_email.test(value);
  var isMobile = reg_mobile.test(value);
  if (isMobile) {
    $(".email_mobile_msg").removeClass('hidden');
    $('.js-captcha').addClass('hidden');
  } else {
    $(".email_mobile_msg").addClass('hidden');
    $('.js-captcha').removeClass('hidden');
  }
  if (isEmail || isMobile) {
    result = true;
  }
  return this.optional(element) || result;
}, Translator.trans('不允许以1开头的11位纯数字'));