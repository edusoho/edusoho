define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("jquery.bootstrap-datetimepicker");
    
    exports.run = function() {
        var validator = new Validator({
            element: '#login-form'
        });

        validator.addItem({
            element: '[name="_username"]',
            required: true
        });

        validator.addItem({
            element: '[name="_password"]',
            required: true
        });

        $(function(){
            //数字验证 
            if ($("#getcode_num").length > 0){
                $("#getcode_num").click(function(){ 
                    $(this).attr("src",$("#getcode_num").data("url")+ "?" + Math.random()); 
                }); 
            }
        });
        if ($("#getcode_num").length > 0){
            validator.addItem({
                element: '[name="captcha_num"]',
                required: true,
                rule: 'alphanumeric remote',
            });
        };
    };

});