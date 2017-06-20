import notify from 'common/notify';

let $form = $('#set-bind-new-form');
let validator = $form.validate({
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
    set_bind_emailOrMobile: {
      required: true,
      es_email: true,
      es_remote: {
        type: 'get'
      }
    }
  }
})

$('#set-bind-new-btn').click(() => {
  if (!validator.form()) {
    return;
  }
  if (!$('#user_terms').find('input[type=checkbox]').attr('checked')) {
    notify('danger',Translator.trans('auth.login_bind_choose.service_agreement_hint'));
    return;
  }
 $('#set-bind-new-btn').button('loading');
  $("#bind-new-form-error").hide();

  $.post($form.attr('action'), $form.serialize(), function (response) {
    if (!response.success) {
      $('#bind-new-form-error').html(response.message).show();
      return;
    }
    notify('success',Translator.trans('auth.login_bind_choose.login_success_hint'));
    window.location.href = response._target_path;

  }, 'json').fail(function () {
    notify('danger',Translator.trans('auth.login_bind_choose.login_failed_hint'));
  }).always(function () {
    $form.find('button[type=submit]').button('reset');
  });
})

$('#user_terms input[type=checkbox]').on('click', function () {
  if ($(this).attr('checked')) {
    $(this).attr('checked', false);
  } else {
    $(this).attr('checked', true);
  };
});
