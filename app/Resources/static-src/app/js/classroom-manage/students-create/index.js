import notify from 'common/notify';

let $modal = $('#student-create-form').parents('.modal');
let $form = $('#student-create-form');
let $table = $('#course-student-list');
let $btn = $("#student-create-form-submit");
let validator = $form.validate({
  onkeyup: false,
  rules: {
    queryfield: {
      required: true,
      remote: {
        url: $('#student-nickname').data('url'),
        type: 'get',
        data: {
          'value': function () {
            return $('#student-nickname').val();
          }
        }
      }
    },
    remark: {
      maxlength: 80,
    },
    price: {
      currency: true,
    }
  },
  messages: {
    queryfield: {
      remote: Translator.trans('请输入学员邮箱/手机号/用户名')
    }
  }
})

$btn.click(() => {
  if (validator.form()) {
    $btn.button('submiting').addClass('disabled');
    $.post($form.attr('action'), $form.serialize(), function (html) {
      $table.find('tr.empty').remove();
      $(html).prependTo($table.find('tbody'));
      $modal.modal('hide');
      let user_name = $('#student-create-form-submit').data('user');
      notify('success', Translator.trans('添加%username%操作成功!', { username: user_name }));
      window.location.reload();
    }).error(function () {
      let user_name = $('#student-create-form-submit').data('user');
      notify('danger', Translator.trans('添加%username%操作失败!', { username: user_name }));
      $btn.button('reset').removeClass('disabled');
    });
  }
})
