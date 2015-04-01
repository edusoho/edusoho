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

        if ($("div .coin-price-modify").length > 0) {
            $("input[name='coinPrice']").on('input',function(){
                var element = $(this);
                var cash_rate= element.data("cashrate");
                var price = element.val();
                var payRmb = parseFloat(price)/parseFloat(cash_rate);
                if(price == ""){
                    $("input[name='price']").attr('value',"0.00");
                }else{
                    $("input[name='price']").attr('value',roundUp(payRmb));
                }
            });
        }
    

        var validator = new Validator({
            element: '#price-form',
            failSilently: true,
            triggerType: 'change'
        });

        validator.addItem({
            element: '[name="price"]',
            rule: 'currency'
        });

        validator.addItem({
            element: '[name="coinPrice"]',
            rule: 'currency'
        });

    };

});