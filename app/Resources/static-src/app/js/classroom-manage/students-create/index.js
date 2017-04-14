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
      notify('success', Translator.trans('添加成功!'));
      window.location.reload();
    }).error(function () {
      notify('danger', Translator.trans('添加失败!'));
      $btn.button('reset').removeClass('disabled');
    });
  }
})
