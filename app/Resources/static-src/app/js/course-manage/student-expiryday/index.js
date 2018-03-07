import notify from 'common/notify';

let $modal = $('#expiryday-set-form').parents('.modal');
let $form = $('#expiryday-set-form');

let validator = $form.validate({
  rules: {
    expiryDay: {
      positive_integer: true,
    }
  }
});

$('.js-save-expiryday-set-form').click(() => {
  if (validator.form()) {
    $.post($form.attr('action'), $form.serialize(), function () {
      let user_name = $('#submit').data('user');
      notify('success',Translator.trans('course_manage.student_expiryday_extend_success_hint', { name: user_name }));
      $modal.modal('hide');
      window.location.reload();
    }).error(function () {
      let user_name = $('#submit').data('user');
      notify('danger',Translator.trans('course_manage.student_expiryday_extend_failed_hint', { name: user_name }));
    });
  }
});