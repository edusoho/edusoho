import notify from 'common/notify';

const $form = $('#delete-form');
const _window = window;
$form.validate({
  ajax: true,
  currentDom: $('delete-btn'),
  rules: {
    password: {
      required: true,
      minlength: 5,
      maxlength: 20
    }
  },
  messages: {
    password: {
      required: Translator.trans('admin.course.validate_old.password_required_hint')
    }
  },
  submitHandler: function () {
    $('.modal-dialog .js-delete-btn').button('loading');

    $.post($form.attr('action'), $form.serialize(), function (response) {
      if (response.success) {
        console.log($('#delete-btn').data('callback'));
        if ($('#delete-btn').data('callback')) {
          eval('document.' + $('#delete-btn').data('callback'));
        }
      } else {
        $('.js-delete-btn').button('reset');
        $('#delete-form').children('div').addClass('has-error');
        notify('danger', Translator.trans('admin.course.delete_course.check_password_fail_hint'));
      }
    });
  }
});
