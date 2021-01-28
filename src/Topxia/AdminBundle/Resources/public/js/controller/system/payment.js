define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
      $('[name=enabled]').change(function (e) {
        var radio = e.target.value;
        var paymentClose = $('.payment-close');
        var paymentOpen = $('.payment-open');

        $('.submit-error').addClass('hidden');
        if (radio == '1') {
          paymentOpen.removeClass('hidden');
          paymentClose.addClass('hidden');
        } else {
          paymentOpen.addClass('hidden');
          paymentClose.removeClass('hidden');
        }
      })

        var validator = new Validator({
                element: '#payment-form'
            });

        $('[name=alipay_enabled]').change(function(e) {
            var radio = e.target.value;
            var subItem = $(this).parents('fieldset').children('[data-sub="alipay"]');
          
            if (radio == '1') {
                $('.submit-error').addClass('hidden');
                subItem.removeClass('hidden');
                validator.addItem({
                    element: '[name="alipay_secret"]',
                    required: true,
                    errormessageRequired: Translator.trans('admin.system.payment.alipay_secret_input.message')
                });
                validator.addItem({
                    element: '[name=alipay_key]',
                    required: true,
                    errormessageRequired: Translator.trans('admin.system.payment.alipay_key_input.message')
                });
            } else {
                subItem.addClass('hidden');
                validator.removeItem('[name="alipay_secret"]');
                validator.removeItem('[name="alipay_key"]');
            }
        });

        // $('[name=wxpay_enabled]').change(function(e) {
        //     var radio = e.target.value;
        //     var subItem = $(this).parents('fieldset').children('[data-sub="wxpay"]');

        //     if (radio == '1') {
        //         $('.submit-error').addClass('hidden');
        //         subItem.removeClass('hidden');
        //         validator.addItem({
        //           element: '[name="wxpay_appid"]',
        //           required: true,
        //           errormessageRequired: Translator.trans('admin.system.payment.wxpay_appid_input.message')
        //         });
        //         validator.addItem({
        //             element: '[name="wxpay_secret"]',
        //             required: true,
        //             errormessageRequired: Translator.trans('admin.system.payment.wxpay_secret_input.message')
        //         });
        //         validator.addItem({
        //           element: '[name="wxpay_mp_secret"]',
        //           required: true,
        //           errormessageRequired: Translator.trans('admin.system.payment.wxpay_mp_secret_input.message')
        //         });
        //         validator.addItem({
        //             element: '[name=wxpay_account]',
        //             required: true,
        //             errormessageRequired: Translator.trans('admin.system.payment.wxpay_account_input.message')
        //         });
        //         validator.addItem({
        //             element: '[name=wxpay_key]',
        //             required: true,
        //             errormessageRequired: Translator.trans('admin.system.payment.wxpay_key_input.message')
        //         });
        //     } else {
        //         subItem.addClass('hidden');
        //         validator.removeItem('[name="wxpay_appid"]');
        //         validator.removeItem('[name="wxpay_secret"]');
        //         validator.removeItem('[name="wxpay_mp_secret"]');
        //         validator.removeItem('[name="wxpay_account"]');
        //         validator.removeItem('[name="wxpay_key"]');
        //     }
        // });

        // $('[name=heepay_enabled]').change(function(e) {
        //     var radio = e.target.value;
        //     var subItem = $(this).parents('fieldset').children('[data-sub="heepay"]');

        //     if (radio == '1') {
        //         $('.submit-error').addClass('hidden');
        //         subItem.removeClass('hidden');
        //         validator.addItem({
        //             element: '[name="heepay_key"]',
        //             required: true,
        //             errormessageRequired: Translator.trans('请输入PID')
        //         });
        //         validator.addItem({
        //             element: '[name=heepay_secret]',
        //             required: true,
        //             errormessageRequired: Translator.trans('请输入商户平台Key')
        //         });
        //     } else {
        //         subItem.addClass('hidden');
        //         validator.removeItem('[name="heepay_key"]');
        //         validator.removeItem('[name="heepay_secret"]');
        //     }
        // });

        // $('[name=quickpay_enabled]').change(function(e) {
        //     var radio = e.target.value;
        //     var subItem = $(this).parents('fieldset').children('[data-sub="quickpay"]');
            
        //     if (radio == '1') {
        //         $('.submit-error').addClass('hidden');
        //         subItem.removeClass('hidden');
        //         validator.addItem({
        //             element: '[name="quickpay_key"]',
        //             required: true,
        //             errormessageRequired: Translator.trans('请输入PID')
        //         });
        //         validator.addItem({
        //             element: '[name=quickpay_secret]',
        //             required: true,
        //             errormessageRequired: Translator.trans('请输入Key')
        //         });
        //         validator.addItem({
        //             element: '[name=quickpay_aes]',
        //             required: true,
        //             errormessageRequired: Translator.trans('请输入Aes')
        //         });
        //     } else {
        //         subItem.addClass('hidden');
        //         validator.removeItem('[name="quickpay_key"]');
        //         validator.removeItem('[name="quickpay_secret"]');
        //         validator.removeItem('[name="quickpay_aes"]');
        //     }
        // });

        $('[name=llpay_enabled]').change(function(e) {
            var radio = e.target.value;
            var subItem = $(this).parents('fieldset').children('[data-sub="llpay"]');

            if (radio == '1') {
                $('.submit-error').addClass('hidden');
                subItem.removeClass('hidden');
                validator.addItem({
                    element: '[name="llpay_key"]',
                    required: true,
                    errormessageRequired: Translator.trans('admin.system.payment.llpay_key_input.message')
                });
                validator.addItem({
                    element: '[name=llpay_accessKey]',
                    required: true,
                    errormessageRequired: Translator.trans('admin.system.payment.llpay_accessKey_input.message')
                });
                validator.addItem({
                    element: '[name=llpay_secretKey]',
                    required: true,
                    errormessageRequired: Translator.trans('admin.system.payment.llpay_secretKey_input.message')
                });
            } else {
                subItem.addClass('hidden');
                validator.removeItem('[name="llpay_key"]');
                validator.removeItem('[name="llpay_accessKey"]');
                validator.removeItem('[name="llpay_secretKey"]');
            }
        });

        $('input[name="alipay_enabled"]:checked').change();
        //$('input[name="wxpay_enabled"]:checked').change();
        // $('input[name="heepay_enabled"]:checked').change();
        // $('input[name="quickpay_enabled"]:checked').change();
        $('input[name="llpay_enabled"]:checked').change();

        $('#payment-btn').on('click', function(){
          if ($('[name=enabled]:checked').val() == '1' && $('[name=alipay_enabled]:checked').val() == '0' && $('[name=wxpay_enabled]:checked').val() == '0' && $('[name=llpay_enabled]:checked').val() == '0') {
            $('.submit-error').removeClass('hidden');
            return false;
          }
          $('.submit-error').addClass('hidden');
          return true;
        })
    };

});
