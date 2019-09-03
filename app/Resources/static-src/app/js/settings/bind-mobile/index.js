import SmsSender from 'app/common/widget/sms-sender';
import notify from 'common/notify';
import Drag from 'app/common/drag';

let $form = $('#bind-mobile-form');
let smsSend = '.js-sms-send';
let $smsCode = $(smsSend);
let drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
  limitType: 'web_register'
}) : null;

$form.validate({
  currentDom: '#submit-btn',
  ajax: true,
  rules: {
    password: {
      required: true,
      es_remote: {
        type: 'post'
      },
    },
    mobile: {
      required: true,
      phone: true,
      es_remote: {
        type: 'get',
        callback: (bool) => {
          if (bool) {
            $smsCode.removeAttr('disabled');
          } else {
            $smsCode.attr('disabled', true);
          }
        }
      },
    },
    sms_code: {
      required: true,
      unsigned_integer: true,
      es_remote: {
        type: 'get',
      },
    },
  },
  messages: {
    sms_code: {
      required: Translator.trans('site.captcha_code.required')
    }
  },
  submitSuccess(data) {
    notify('success', Translator.trans(data.message));
    $('.modal').modal('hide');
    window.location.reload();
  },
  submitError(data) {
    notify('danger',  Translator.trans(data.responseJSON.message));
  }
});

if (drag) {
  drag.on('success', function(token){
    $smsCode.removeClass('disabled').attr('disabled', false);
  });
}

$smsCode.on('click', function() {
  $smsCode.attr('disabled', true);
  new SmsSender({
    element: smsSend,
    url: $smsCode.data('url'),
    smsType: 'sms_bind',
    captcha: true,
    captchaValidated: true,
    captchaNum: 'dragCaptchaToken',
    preSmsSend: function() {
      return true;
    },
    error: function(error) {
      drag.initDragCaptcha();
    },
    additionalAction: function(ackResponse) {
      if (ackResponse == 'captchaRequired') {
        $smsCode.attr('disabled', true);
        $('.js-drag-jigsaw').removeClass('hidden');
        if(drag) {
          drag.initDragCaptcha();
        }
        return true;
      }
      return false;
    }
  });
});
