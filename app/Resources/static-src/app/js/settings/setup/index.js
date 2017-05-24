import notify from 'common/notify';

let $form = $('#setup-form');
let $btn = $('.js-submit-setup-form');
if ($form.length) {
  let validator = $form.validate({
    email: {
      required: true,
      es_email: true,
      es_remote: {
        type: 'POST',
      }
    },
    nickname: {
      required:true,
      minlength: 4,
      maxlength: 18,
      nickname: true,
      chinese_alphanumeric: true,
      es_remote: {
        type: 'get',
      }
    },
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
