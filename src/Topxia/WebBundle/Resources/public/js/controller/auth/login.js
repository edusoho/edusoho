define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("placeholder")
    require("jquery.bootstrap-datetimepicker");
    exports.run = function() {

        var password_placeholder_for_ie8 = function (){
           $('#login_password').hide();

            $('#fake_login_password').show();

            $('#fake_login_password').focus(function(){
                $(this).hide(); 
                $('#login_password').show().focus(); 
            });

            $('#login_password').blur(function(){
                if($(this).val() == ""){ 
                    $(this).hide(); 
                    $('#fake_login_password').show(); 
                }
            });
       };
       
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

    };

});