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
  $('.js-find-by-email').addClass('active');
  $('.js-find-by-mobile').removeClass('active');
  makeValidator('email');
  $('#password-reset-by-mobile-form').hide();
  $('#password-reset-form').show();
})

$('.js-find-by-mobile').click(function () {
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
            type: 'get'
          }
        },
        'sms_code': {
          required: true,
          integer: true,
          es_remote: true,
        }
      }
    })
    $form.on('focusout.validate', () => {
      if ($form.validate().element('[name="mobile"]')) {
        $('.js-sms-send').removeClass('disabled');
      } else {
        $('.js-sms-send').addClass('disabled');
      }
    })

    // validator = new Validator({
    //   element: '#password-reset-by-mobile-form',
    //   onFormValidated: function (err, results, form) {
    //     if (err == false) {
    //       $('#password-reset-by-monile-form').find("[type=submit]").button('loading');
    //     } else {
    //       $('#alertxx').hide();
    //     };
    //   }
    // });

    // validator.addItem({
    //   element: '[name="mobile"]',
    //   required: true,
    //   rule: 'phone email_or_mobile_remote',
    //   onItemValidated: function (error, message, eleme) {
    //     if (error) {
    //       $('.js-sms-send').addClass('disabled');
    //       return;
    //     } else {
    //       $('.js-sms-send').removeClass('disabled');
    //     }
    //   }
    // });

    // validator.addItem({
    //   element: '[name="sms_code"]',
    //   required: true,
    //   triggerType: 'submit',
    //   rule: 'integer fixedLength{len:6} remote',
    //   display: Translator.trans('短信验证码')
    // });
  }

  $("[type='submit']").click(()=>{
    if(validator.form()) {
      $(event.currentTarget).button('loading');
      $form.submit();
    }else {
       $('#alertxx').hide();   
    }
  })
};

