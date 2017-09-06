import SmsSender from 'app/common/widget/sms-sender';
let validator = null;
let $form = null;

if ($('.js-find-password li').length > 1) {
  $('.js-find-by-email').click(function () {
    if (!$('.js-find-by-email').hasClass('active')) {
      $('#alertxx').hide();
    }
  });
  $('.js-find-by-mobile').click(function () {
    if (!$('.js-find-by-mobile').hasClass('active')) {
      $('#alertxx').hide();
    }
  });
}

makeValidator('email');

$('.js-find-by-email').click(function () {
  validator = null;
  $('.js-find-by-email').addClass('active');
  $('.js-find-by-mobile').removeClass('active');
  makeValidator('email');
  $('#password-reset-by-mobile-form').hide();
  $('#password-reset-form').show();
})

$('.js-find-by-mobile').click(function () {
  validator = null;
  $('.js-find-by-email').removeClass('active');
  $('.js-find-by-mobile').addClass('active');
  makeValidator('mobile');
  $('#password-reset-form').hide();
  $('#password-reset-by-mobile-form').show();

})

$('.js-sms-send').click(()=>{
  var smsSender = new SmsSender({
    element: '.js-sms-send',
    url: $('.js-sms-send').data('smsUrl'),
    smsType: $('.js-sms-send').data('smsType'),
    preSmsSend: function () {
      var couldSender = true;
      return couldSender;
    }
  });
})

function makeValidator(type) {
  if ('email' == type) {
    $form = $('#password-reset-form');
    validator = $form.validate({
      rules: {
        '[name="form[email]"]': {
          required: true,
          email: true,
        }
      }
    });
  }

  if ('mobile' == type) {
    $form = $('#password-reset-by-mobile-form');
    validator = $form.validate({
      rules: {
        'mobile': {
          required: true,
          phone: true,
          es_remote: {
            type: 'get',
            callback: (bool) => {
              if (bool) {
                $('.js-sms-send').removeClass('disabled');
              } else {
                $('.js-sms-send').addClass('disabled');
              }
            }
          }
        },
        'sms_code': {
          required: true,
          unsigned_integer: true,
          rangelength: [6, 6],
          es_remote: {
            type: 'get'
          },
        },
      },
      messages: {
        sms_code: {
          required: Translator.trans('auth.password_reset.sms_code_required_hint'),
          rangelength: Translator.trans('auth.password_reset.sms_code_validate_hint'),
        }
      }
    })
  }
};