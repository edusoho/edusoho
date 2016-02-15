define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Widget = require('widget');
    var SmsSender = require('../widget/sms-sender');

    var CaptchaModal = Widget.extend({
        _captchaValidated: false,
        _validator:null,
        attrs: {
            dataTo: 'mobile',
            smsType: 'sms_registration',
            captchaNum: 'captcha_num',
        },
        events: {
            'click #getcode_num': 'changeCaptcha'
        },

        setup: function() {
            var $form = this.element;
            var self = this;
            self._captchaValidated = false;

            this._validator = new Validator({
                element: $form,
                autoSubmit: false,
                onFormValidated: function(error, results, $form) {
                    if (error) {
                        return false;
                    }

                    $.get($form.attr('action'), {value:$('#captcha_num_modal').val()}, function(response) {
                        
                        if (response.success) {
                            $form.parents('.modal').modal('hide');
                            // $form.find('#getcode_num').attr("src",$("#getcode_num").data("url")+ "?" + Math.random()); 
                            self._captchaValidated = true;

                            var smsSender = new SmsSender({
                                element: '.js-sms-send',
                                url: $('.js-sms-send').data('smsUrl'),
                                smsType: self.get('smsType'),
                                dataTo : self.get('dataTo'),
                                captchaNum : self.get('captchaNum'),
                                captcha: true,
                                captchaValidated: self._captchaValidated,
                                preSmsSend: function(){
                                    var couldSender = true;

                                    return couldSender;
                                }      
                            });
                            smsSender.undelegateEvents('.js-sms-send', 'click');

                        } else {
                            self._captchaValidated = false;

                            $form.find('#getcode_num').attr("src",$("#getcode_num").data("url")+ "?" + Math.random());
                            
                            $form.find('.help-block').html('<span class="text-danger">验证码错误</span>');
                            $form.find('.help-block').show();
                        }
                    }, 'json');
                }
            });

            this._validator.addItem({
                element: '#captcha_num_modal',
                required: true,
                rule: 'alphanumeric',
                display: '验证码',
                errormessageRequired:'请输入验证码',
            });
        },
        
        changeCaptcha: function(e) {
            var $code = $(e.currentTarget);
            $code.attr("src",$code.data("url")+ "?" + Math.random()); 
        },

        getCaptchaValidated: function() {
            return this._captchaValidated;
        },



    });

    module.exports = CaptchaModal;

});