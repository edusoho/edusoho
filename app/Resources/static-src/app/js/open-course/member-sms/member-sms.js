import SmsSender from 'app/common/widget/sms-sender';
import notify from 'common/notify';

class MembarSMS {
  constructor(options) {
    this.$element = $(options.element);
    this.formSubmit = options.formSubmit;
    this.$formSubmit = $(this.formSubmit);
    this.validator = null;

    this.initEvent();
    this.initValidator();
  }

  initEvent() {
    this.$element.on('click', '.js-modify-mobile', event => this.onModifyMobile(event));
    this.$element.on('click', '.js-get-code', event => this.onGetCode(event));
    this.$element.on('click', '.js-sms-send', event => this.onSmsSend(event));
    this.$formSubmit.on('click', event => this.onFormSubmit(event));
  }

  onModifyMobile(event) {
    let $this = $(event.currentTarget);
    $this.hide();
    
    this.$element.find('input[name="mobile"]').attr('readonly',false);
    this.$element.find('.form-group').show();

    this.addRules();
  }

  onGetCode(event) {
    let $this = $(event.currentTarget);
    $this.attr('src', $this.data('url') + '?' + Math.random());
  }

  onSmsSend(event) {
    if (!this.isCanSmsSend()) return;
    
    let $this = $(event.currentTarget);
    new SmsSender({
      element: '.js-sms-send',
      url: $this.data('url'),
      smsType: 'system_remind',
      captchaValidated: true,
      captchaNum: 'captcha_code',
      captcha: true,
    });
  }

  onFormSubmit(event) {
    if (this.validator.form()) {
      this.$element.submit();
    }
  }

  isCanSmsSend() {
    let isMobile = this.$element.validate().element($('[name="mobile"]')); 
    if (!isMobile) {
      return false;
    }

    let isCaptcha = this.$element.validate().element($('[name="captcha_code"]'));
    if (!isCaptcha) {
      return false;
    }

    return true;
  }

  initValidator() {
    let $form = this.$element;
    this.validator = this.$element.validate({
      ajax: true,
      currentDom: this.formSubmit,
      submitSuccess(data) {
        $form.closest('.modal').modal('hide');
       
        $('#alert-btn').addClass('hidden');
        $('#alerted-btn').removeClass('hidden');
        $('.js-member-num span').text(parseInt(data.number));
      },
      submitError(data) {
        notify('danger',  Translator.trans(data.responseJSON.message));
      }
    });

    if (this.$element.find('input[name="mobile"]').attr('readonly') != 'readonly') {
      this.addRules();
    }
  }

  addRules() {
    $('[name="mobile"]').rules('add', {
      required: true,
      phone: true,
      es_remote: true
    });
    $('[name="captcha_code"]').rules('add', {
      required: true,
      alphanumeric: true,
      es_remote: true
    });
    $('[name="sms_code_modal"]').rules('add', {
      required: true,
      maxlength: 6,
      minlength: 6,
      es_remote: true
    });
  }
}

export default MembarSMS;