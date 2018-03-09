import notify from 'common/notify';

class PayPasswordModal {
  constructor(props) {
    this.element = $(props.element);
    this.currentDom = props.currentDom;
    this.init();
  }
  init() {
    this.initEvent();
    this.validate();
  }
  validate() {
    let currentDom = this.currentDom;
    let validator = this.element.validate({
      ajax: true,
      currentDom,
      rules: {
        'form[currentUserLoginPassword]': {
          required: true,
          es_remote: true
        },
        'form[newPayPassword]': {
          required: true,
          maxlength: 20,
          minlength: 5,
        },
        'form[confirmPayPassword]': {
          required: true,
          equalTo: '#form_newPayPassword'
        }
      },
      submitError(data) {
        notify('danger', 'pay.security.password.save_fail_hint');
      },
      submitSuccess(data) {
        notify('success', data.message);
        setTimeout(function() {
          window.location.reload();
        }, 1000);
      }
    });
    return validator;
  }
  initEvent() {
    $(this.currentDom).on('click', () => {
      if (this.validate().form()) {
        this.element.submit();
      }
    });
  }
}

new PayPasswordModal({
  element: '#settings-pay-password-form',
  currentDom: '.js-submit-form'
});