import notify from 'common/notify';
let $modal = $('#student-remark-form').parents('.modal');
let $form = $('#student-remark-form');

let validator = $form.validate({
  rules: {
    remark: {
      required: false,
      maxlength: 80,
    }
  },
  messages: {
    remark: {
      maxlength: Translator.trans('备注字数不超过80')
    }
  }
});

$('.js-student-remark-save-btn').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loadding');
    $.post($form.attr('action'), $form.serialize(), function (html) {
      let $html = $(html);
      $('#' + $html.attr('id')).replaceWith($html);
      $modal.modal('hide');
      let user_name = $form.data('user');
      notify('success', Translator.trans('备注%username%成功', { username: user_name }));
    }).error(function () {
      let user_name = $form.data('user');
      notify('danger', Translator.trans('备注%username%失败，请重试！', { username: user_name }));
    });
  }
})