import notify from 'common/notify';

let $form = $('#setup-form');
let $btn = $('.js-submit-setup-form');
if ($form.length) {
  let validator = $form.validate({
    email: {
      required: true,
      email: true,
      remote_return_array: true,
    },
    nickname: {
      required: true,
      chinese_alphanumeric: true,
      maxlength: 18,
      minlength: 4,
      remote_return_array: true,
    }
  })

  $btn.click(() => {
    if (validator.form()) {
      $btn.button('loadding');
      $.post($form.attr('action'), $form.serialize(), function () {
        notify('success', Translator.trans('设置帐号成功，正在跳转'));
        window.location.href = $btn.data('goto');
      }).error(function () {
        $btn.button('reset');
        notify('danger', Translator.trans('设置帐号失败，请重试'));
      });
    }
  })
}
