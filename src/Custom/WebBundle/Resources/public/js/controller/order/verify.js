define(function(require, exports, module) {
    var Widget = require('widget');
        require("chineserp-jquery");
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var OrderVerify = Widget.extend({
        attrs: {
             validator : null
        },

        events: {
            "blur [name=truename]": "_onBlurTruename",
            "blur [name=mobile]": "_onBlurMobile",
            "change [name=needInvoice]": "_onChangeNeedInvoice",
            "change [name=invoiceTitle]": "_onChangeInvoiceTitle"
        },
        setup: function() {
            this._initForm();
            this._initTotal();
        },
        _onChangeNeedInvoice: function(e) {
            var $radios = $(e.currentTarget);
            if($radios.val() == 'yes') {

                this.$('[data-role=address]').slideDown('normal');
            } else {
                this.$('[data-role=address]').slideUp('normal');
            }
        },

        _onBlurTruename: function(e) {
            $input = $(e.currentTarget);
            $truename = $input.val();

            if ($truename != '') {
                $.post($input.data('url'),{truename:$truename},function(){

                });
            };

        },

        _onBlurMobile: function(e) {
           $input = $(e.currentTarget);
           $mobile = $input.val();

           if ($mobile != '') {
               $.post($input.data('url'),{mobile:$mobile},function(){

               });
           };
        },

        _initForm: function() {
            var $form = this.element;
            this.set('form', $form);
            this.set('validator', this._createValidator($form));
        },

        _createValidator: function($form){
            var self = this;
            var validator = new Validator({
                element: $form,
                autoSubmit: true,
                onFormValidated: function(error){
                    if (error) {
                      return false;
                    }
                    $('#form-submit-btn').button('submiting').addClass('disabled');
                }
            });

            validator.addItem({
                element: '[name=mobile]',
                required: false,
                rule:'phone',
                display: '手机号码'
            });

            return validator;
        },

        _onChangeInvoiceTitle: function(e) {
            var $target = $(e.currentTarget);
            $.post($target.data('updateUrl'), {title:$target.val()});
        },

        _initTotal :function() {
            var priceTotal = 0;
            $.each($.find('[data-role=price]'),function(index,item){
                $price = $(item).text();
                priceTotal += Number($price)
            });

            this.$('[data-role=total-price]').html(priceTotal.toFixed(2));
            this.$('[name=accountPayable]').val(priceTotal.toFixed(2));
        }
    });

    new OrderVerify({
        'element': '#order-verify-form'
    });

});