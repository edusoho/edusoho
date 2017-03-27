define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('jquery.select2-css');
    require('jquery.select2');
    require("jquery.bootstrap-datetimepicker");
    var Notify = require('common/bootstrap-notify');

    function roundUp(amount){
        return (amount*100/100).toFixed(2);
    }

    exports.run = function() {
        var $form = $("#price-form");

        var validator = new Validator({
            element: $form,
            failSilently: true,
            triggerType: 'change'
        });

        $("input[name=buyExpiryTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });

        $("input[name=buyExpiryTime]").datetimepicker('setStartDate', new Date);

        validator.addItem({
            element: '[name="price"]',
            rule: 'currency',
            onItemValidated: function(error, message, element) {
                if (error) {
                    return ;
                }

                var $element = $(element);

                if (!$element.hasClass('course-current-price')) {
                    return ;
                }

                $discountPrice = $form.find('.course-discount-price');
                if ($discountPrice.length == 0) {
                    return ;
                }

                $discountPrice.text(roundUp($element.val() * $discountPrice.data('discount') / 10));
            }
        });

        validator.addItem({
            element: '[name="coinPrice"]',
            rule: 'currency',
            onItemValidated: function(error, message, element) {
                if (error) {
                    return ;
                }

                var $element = $(element);

                if (!$element.hasClass('course-current-price')) {
                    return ;
                }

                $discountPrice = $form.find('.course-discount-price');
                if ($discountPrice.length == 0) {
                    return ;
                }

                $discountPrice.text(roundUp($element.val() * $discountPrice.data('discount') / 10));
            }
        });

        $('input[name=tryLookable]').change(function(){
            if($(this).val()=="1"){
                $('#tryLookTimeGroup').removeClass('hide');
            }else {
                $('#tryLookTimeGroup').addClass('hide');
            }
        });

        $('input[name=enableBuyExpiryTime]').change(function(){
            if($(this).val()=="1"){
                $('#buyExpiryTime').removeClass('hide');
                validator.addItem({
                    element: '[name=buyExpiryTime]',
                    required: true,
                    display: '购买截止日期'
                });
            }else {
                $('#buyExpiryTime').addClass('hide');
                validator.removeItem('[name=buyExpiryTime]');
            }
        });
    };

});