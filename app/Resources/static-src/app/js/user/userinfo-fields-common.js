import SmsSender from 'app/common/widget/sms-sender';
import notify from 'common/notify';

export default class UserInfoFieldsItemValidate {
  constructor(options) {
    this.validator = null;
    this.$element = $(options.element);
    this.setup();
  }

  setup() {
    this.createValidator();
    this.initComponents();
    this.smsCodeValidate();
    this.initEvent();
  }

  initEvent()
  {
    this.$element.on('click', '#getcode_num', (event) => this.changeCaptcha(event));
    this.$element.on('click', '.js-sms-send', (event) => this.sendSms(event));
  }

  initComponents() {
    $('.date').each(function () {
      $(this).datetimepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        minView: 2,
        language: document.documentElement.lang
      });
    });
  }

  createValidator() {
    this.validator = this.$element.validate({
      currentDom: '#form-submit-btn',
      rules: {
        email: {
          required: true,
          email: true,
          remote: {
            url: $('#email').data('url'),
            type: 'get',
            data: {
              'value': function () {
                return $('#email').val();
              }
            }
          }
        },
        mobile: {
          required: true,
          phone: true,
          remote: {
            url: $('#mobile').data('url'),
            type: 'get',
            data: {
              'value': function () {
                return $('#mobile').val();
              }
            }
          }
        },
        truename: {
          required: true,
          chinese_alphanumeric: true,
          minlength: 2,
          maxlength: 36,
        },
        qq: {
          required: true,
          qq: true,
        },
        idcard: {
          required: true,
          idcardNumber: true,
        },
        gender: {
          required: true,
        },
        company: {
          required: true,
        },
        job: {
          required: true,
        },
        weibo: {
          required: true,
          url: true,
        },
        weixin: {
          required: true,
        }
      },
      messages: {
        gender: {
          required: Translator.trans('site.choose_gender_hint'),
        },
        mobile: {
          phone: Translator.trans('validate.phone.message'),
        }
      },
      submitHandler: form => {
        if ($(form).valid()) {
          $.post($(form).attr('action'), $(form).serialize(), resp => {
            if (resp.url) {
              location.href = resp.url;
            } else {
              notify('success', Translator.trans('site.save_success_hint'));
              $('#modal').modal('hide');
            }

          });
        }
      }
    });
    this.getCustomFields();
  }

  smsCodeValidate() {
    if ($('.js-captch-num').length > 0) {
      
      //$('.js-captch-num').find('#getcode_num').attr("src", $("#getcode_num").data("url") + "?" + Math.random());

      $('input[name="captcha_num"]').rules('add', {
        required: true,
        alphanumeric: true,
        es_remote: {
          type: 'get',
          callback: function (bool) {
            if (bool) {
              $('.js-sms-send').removeClass('disabled');
            } else {
              $('.js-sms-send').addClass('disabled');
              $('.js-captch-num').find('#getcode_num').attr('src',$('#getcode_num').data('url')+ '?' + Math.random());
            }
          }
        },
        messages: {
          required: Translator.trans('site.captcha_code.required'),
          alphanumeric: Translator.trans('json_response.verification_code_error.message'),
        }
      });

      $('input[name="sms_code"]').rules('add', {
        required: true,
        unsigned_integer: true,
        es_remote: {
          type: 'get',
        },
        messages: {
          required: Translator.trans('validate.sms_code_input.message'),
        }
      });
    }
  }

  sendSms() {

    new SmsSender({
      element: '.js-sms-send',
      url: $('.js-sms-send').data('smsUrl'),
      smsType: 'sms_bind',
      dataTo: 'mobile',
      captchaNum: 'captcha_num',
      captcha: true,
      captchaValidated: $('input[name="captcha_num"]').valid(),
      preSmsSend: function () {
        let couldSender = true;
        return couldSender;
      }
    });
  }

  getCustomFields() {
    for (var i = 1; i <= 5; i++) {
      $(`[name="intField${i}"]`).rules('add', {
        required: true,
        positive_integer: true,
      });
      $(`[name="floatField${i}"]`).rules('add', {
        required: true,
        float: true,
      });
      $(`[name="dateField${i}"]`).rules('add', {
        required: true,
        date: true,
      });
    }
    for (i = 1; i <= 10; i++) {
      $(`[name="varcharField${i}"]`).rules('add', {
        required: true,
      });
      $(`[name="textField${i}"]`).rules('add', {
        required: true,
      });
    }
  }

  changeCaptcha(e) {
    var $code = $(e.currentTarget);
    $code.attr('src', $code.data('url') + '?' + Math.random());
  }
}
