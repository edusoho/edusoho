define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');

    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var validator = new Validator({
            element: '#course-buy-form'           
        });

        validator.addItem({
            element: '[name="promoCode"]',
            required: false,
            rule: 'remote',
            hideMessage:function(msg,ele,eve){
                console.log(typeof msg);
                if(null != msg ){
                    $("#promoCode_info").html(msg);
                    $("#promoCode_info").addClass('text-color-green');
                }
            }
          
        });

    };

});