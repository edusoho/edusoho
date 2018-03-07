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
      maxlength: Translator.trans('course_manage.student_remark_validate_error_hint')
    }
  }
});

$('.js-student-remark-save-btn').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loadding');
    $.post($form.attr('action'), $form.serialize(), function (resp) {
      $modal.modal('hide');
      let user_name = $form.data('user');
      notify('success', Translator.trans('course_manage.student_remark_success_hint', { username: user_name }), {delay:1000, onClose: function () {
        window.location.reload();
      }});
    }).error(function () {
      let user_name = $form.data('user');
      notify('danger', Translator.trans('course_manage.student_remark_failed_hint', { username: user_name }));
    });
  }
});