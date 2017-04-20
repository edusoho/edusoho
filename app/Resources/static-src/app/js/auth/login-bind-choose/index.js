import notify from 'common/notify';

let $form = $('#set-bind-new-form');
let validator = $form.validate({
  rules: {
    set_bind_emailOrMobile: {
      required: true,
      email: true,
      es_remote: {
        type: 'get'
      }
    },
    nickname: {
      required: true,
      chinese_alphanumeric: true,
      minlength: 4,
      maxlength: 8,
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
    notify('danger',Translator.trans('勾选同意此服务协议，才能继续注册！'));
    return;
  }
  $form.find('[type=submit]').button('loading');
  $("#bind-new-form-error").hide();

  $.post($form.attr('action'), $form.serialize(), function (response) {
    if (!response.success) {
      $('#bind-new-form-error').html(response.message).show();
      return;
    }
    notify('success',Translator.trans('登录成功，正在跳转至首页！'));
    window.location.href = response._target_path;

  }, 'json').fail(function () {
    notify('danger',Translator.trans('登录失败，请重新登录后再试！'));
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