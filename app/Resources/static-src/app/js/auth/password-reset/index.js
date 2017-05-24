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
          rangelength: Translator.trans('的长度必须等于6'),
        }
      }
    })
  }
};

