import SmsSender from 'app/common/widget/sms-sender';
import Drag from 'app/common/drag';
import Coordinate from 'app/common/coordinate';

let smsSend = '.js-sms-send';
let $smsCode = $(smsSend);
let $form = $('#settings-find-pay-password-form');
let drag = $('#drag-btn').length ? new Drag($('#drag-btn'), $('.js-jigsaw'), {
  limitType: 'web_register'
}) : null;
let validator = $form.validate({
  rules: {
    sms_code: {
      required: true,
      unsigned_integer: true,
      es_remote: true,
    }
  },
  messages: {
    sms_code: {
      required: Translator.trans('site.captcha_code.required'),
    }
  }
});

$('#submit-btn').click(() => {
  if (validator.form()) {
    $form.submit();
  }
});

if (drag) {
  drag.on('success', function(token){
    $smsCode.removeClass('disabled').attr('disabled', false);
  });
}

$smsCode.on('click', () => {
  const coordinate = new Coordinate();
  const encryptedPoint = coordinate.getCoordinate(event, $('meta[name=csrf-token]').attr('content'));
  $smsCode.attr('disabled', true);
  new SmsSender({
    element: smsSend,
    url: $smsCode.data('smsUrl'),
    smsType: $smsCode.data('smsType'),
    captcha: true,
    captchaValidated: true,
    encryptedPoint: encryptedPoint,
    captchaNum: 'dragCaptchaToken',
    preSmsSend: () => {
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

