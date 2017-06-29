define(function(require, exports, module) {
    
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        
        var validator = new Validator({
            element: '#coin-model-form',
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#next').button('submiting').addClass('disabled');
                $('#save').button('submiting').addClass('disabled');
            },
            failSilently: true
        });

        $('.model').on('click',function(){

            $('.model').removeClass("btn-primary");
            $(this).addClass("btn-primary");
            $('[name="cash_model"]').attr('value',$(this).data('modle'));

            if($(this).data('modle')!="none"){

                $('#next').removeClass('hidden');

                $('#save').addClass('hidden');

            }else{
                
                $('#next').addClass('hidden');

                $('#save').removeClass('hidden');
            }

            if($(this).data('modle')=="deduction"){

                $('.deduction').removeClass('hidden');

                $('.none').addClass('hidden');

                $('.currency').addClass('hidden');
            }

            if($(this).data('modle')=="currency"){

                $('.currency').removeClass('hidden');

                $('.none').addClass('hidden');

                $('.deduction').addClass('hidden');
            }

            if($(this).data('modle')=="none"){

                $('.none').removeClass('hidden');

                $('.currency').addClass('hidden');

                $('.deduction').addClass('hidden');


            }

        });

        validator.addItem({
            element: '[name="cash_rate"]',
            required: true,
            rule: 'decimal arithmetic_number' 
        });

    };

    
});