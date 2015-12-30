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
    
        require('./header').run();

        var $form = $("#price-form");

        var validator = new Validator({
            element: $form,
            failSilently: true,
            triggerType: 'change'
        });

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

        Validator.addRule('tryLookTimeVali', function(options) {
            
            var tryLookTime = $('select[name=tryLookTime]').val();

            var tryLookable = $('input[name=tryLookable]:checked').val();
            if(tryLookable == "1"){
                return true;
            }else if(tryLookTime == ''){
                console.log('return false');
                return false;
            }
            return true;
        }, '请选择试看时间');

        validator.addItem({
            element: '[name=tryLookTime]',
            rule: 'tryLookTimeVali'
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
                $('#tryLookTimeGroup').attr('class','hide');
            }
        });

    };

});