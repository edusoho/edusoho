define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("common/jquery.placeholder.js")
    require("jquery.bootstrap-datetimepicker");
    exports.run = function() {
        $('input, textarea').placeholder(); 

        var password_placeholder_for_ie8 = function (){
           $('#login_password').hide();

            // Show the fake pass (because JS is enabled)
            $('#fake_login_password').show();

            // On focus of the fake password field
            $('#fake_login_password').focus(function(){
                $(this).hide(); //  hide the fake password input text
                $('#login_password').show().focus(); // and show the real password input password
            });

            // On blur of the real pass
            $('#login_password').blur(function(){
                if($(this).val() == ""){ // if the value is empty, 
                    $(this).hide(); // hide the real password field
                    $('#fake_login_password').show(); // show the fake password
                }
                // otherwise, a password has been entered,
                // so do nothing (leave the real password showing)
            });
       }();
       
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