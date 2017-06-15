import notify from 'common/notify';

let $form = $('#write-invite-code');
let $modal = $form.parents('.modal');
let url = $('#create-btn').data('url');

let validator = $form.validate({
  rules: {
    inviteCode: {
      required: true,
      reg_inviteCode: true,
    },
  },
  messages: {
    inviteCode: {
      required: Translator.trans('请输入邀请码'),
      reg_inviteCode: Translator.trans('邀请码必须是5位数字、英文字母组成')
    }
  }
})

$('#create-btn').click(() => {
  if (validator.form()) {
    $.post($form.attr('action'), $form.serialize(), function (response) {
      if (response.success == true) {
        $modal.modal('hide');
        window.location.href = url;
      } else {
        notify('warning',response.message);
        setTimeout(function () {
          window.location.reload();
        }, 1000);
      }
    });
  }

})


