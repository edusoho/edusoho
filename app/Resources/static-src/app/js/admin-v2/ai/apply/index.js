let $form = $('#apply-form');
let validator = $form.validate({
  rules: {
    name: {
      required: true
    },
    mobile: {
      required: true
    },
  },
  messages: {
    name: {
      required: '请输入姓名'
    },
    mobile: {
      required: '请输入手机号'
    },
  }
});

$('.js-apply-btn').on('click', e => {
  if (validator.form()) {
    $.post($form.attr('action'), $form.serialize(), resp => {});
    $('.apply-modal-title').hide();
    $('.apply-success').css('display', 'block');
    $('.apply-modal-form').css('display', 'none');
  }
});

$('.js-confirm').on('click', e => {
  let $target = $(e.currentTarget);
  let $modal = $('#modal');
  $modal.load($target.data('url'));
  $modal.modal('show');
  $('#attachment-modal').modal('hide');
});
