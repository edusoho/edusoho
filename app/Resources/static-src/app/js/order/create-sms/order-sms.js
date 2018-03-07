import SmsSender from 'app/common/widget/sms-sender';

class OrderSms {
  constructor(options) {
    this.$element = $(options.element);
    this.formSubmit = options.formSubmit;
    this.$formSubmit = $(this.formSubmit);

    this.init();
  }

  init() {
    this.initEvent();
    this.initValidator();
  }

  initEvent() {
    this.$element.on('click', '.js-sms-send', event => this.onSmsSend(event));
  }

  onSmsSend() {
    let smsSend = '.js-sms-send';
    new SmsSender({
      element: smsSend,
      url: $(smsSend).data('url'),
      smsType: 'sms_user_pay',
    });
  }

  initValidator() {
    this.$element.validate({
      ajax: true,
      currentDom: this.formSubmit,
      rules: {
        sms_code_modal: {
          required: true,
          maxlength: 6,
          minlength: 6,
          es_remote: true
        }
      },
      submitSuccess(data) {
        let smsCode = $('[name="sms_code_modal"]').val();
        $('[name="sms_code"]').val(smsCode);
        $('#modal').modal('hide');
        $('#order-create-form').submit();
      }
    });
  }
}

export default OrderSms;
