import notify from 'common/notify';

export default class SmsSender {
  constructor(option) {
    this.$element = $(option.element);
    this.validator = 0;
    this.url = option.url ? option.url : '';

    // 发送请求结束后，会先进行此操作, 接受参数为 发送短信返回结果中的 ACK 属性
    if (option.additionalAction) {
      this.additionalAction = option.additionalAction;
    }
    this.error = option.error ? option.error : this.error;
    this.smsType = option.smsType ? option.smsType : '';
    this.captchaNum = option.captchaNum ? option.captchaNum : 'captcha_num';
    this.captcha = option.captcha ? option.captcha : false;
    this.captchaValidated = option.captchaValidated ? option.captchaValidated : false;
    this.dataTo = option.dataTo ? option.dataTo : 'mobile';
    this.setup();
  }

  preSmsSend() {
    return true;
  }

  error() {
  }

  additionalAction(ackResponse) {
    return false;
  }

  setup() {
    this.smsSend();
    console.log('smsSend');
  }
  postData(url, data) {
    var self = this;
    console.log(this.$element);
    var refreshTimeLeft = function() {
      var leftTime = $('#js-time-left').text();
      $('#js-time-left').text(leftTime - 1);
      if (leftTime - 1 > 0) {
        setTimeout(refreshTimeLeft, 1000);
      } else {
        $('#js-time-left').text('');
        $('#js-fetch-btn-text').text(Translator.trans('site.data.get_sms_code_btn'));
        self.$element.removeClass('disabled').attr('disabled', false);
      }
    };
    self.$element.addClass('disabled');
    $.post(url, data, function(response) {
      let ackResponse = 'undefined' != typeof response['ACK'] ? response['ACK'] : '';
      if (self.additionalAction(ackResponse)) {
        // 已在条件中自动处理，不需要额外处理
      } else if (ackResponse == 'ok') {
        $('#js-time-left').text('120');
        $('#js-fetch-btn-text').text(Translator.trans('site.data.get_sms_code_again_btn'));
        if (response.allowance) {
          notify('success', Translator.trans('site.data.get_sms_code_allowance_success_hint', { 'allowance': response.allowance }));
        } else {
          notify('success', Translator.trans('site.data.get_sms_code_success_hint'));
        }

        refreshTimeLeft();
      } else {
        if ('undefined' != typeof response['error']) {
          notify('danger', response['error']);
        } else {
          notify('danger', Translator.trans('site.data.get_sms_code_failure_hint'));
        }
        self.$element.removeClass('disabled').attr('disabled', false);
      }
    }).error(function(error){
      self.error(error);
    });
    return this;
  }

  smsSend() {
    console.log('smsSend...');
    var leftTime = $('#js-time-left').text();
    if (leftTime.length > 0) {
      return false;
    }
    var url = this.url;
    var data = {};
    data.to = $('[name="' + this.dataTo + '"]').val();
    data.sms_type = this.smsType;
    if (this.captcha) {
      data.captcha_num = $('[name="' + this.captchaNum + '"]').val();
      if (!this.captchaValidated) {
        return false;
      }
    }
    data = $.extend(data, data);
    if (this.preSmsSend()) {
      this.postData(url, data);
    }
    return this;
  }
}