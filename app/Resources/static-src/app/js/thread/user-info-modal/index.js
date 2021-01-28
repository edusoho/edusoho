let $form = $('#join-event-form');
import notify from 'common/notify';

$form.validate({
  ajax: true,
  currentDom: '#join-event-btn',
  rules: {
    truename: {
      required: true,
      chinese: true,
      trim: true,
      byte_minlength: 4,
      byte_maxlength: 10
    },
    mobile: {
      required: true,
      phone: true
    }
  },
  submitSuccess() {
    notify('success', Translator.trans('site.save_success_hint'));
    window.location.reload();
  }
});