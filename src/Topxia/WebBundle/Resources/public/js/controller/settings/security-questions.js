define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var validator = new Validator({
            element: '#settings-security-questions-form',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#password-save-btn').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="answer-1"]',
            required: true,
            rule: 'maxlength{max:20}'            
        });
        
        validator.addItem({
            element: '[name="answer-2"]',
            required: true,
            rule: 'maxlength{max:20}'
        });
        
        validator.addItem({
            element: '[name="answer-3"]',
            required: true,
            rule: 'maxlength{max:20}'
        });

        var q1 = $('[name=question-1]'), q2 = $('[name=question-2]'), q3 = $('[name=question-3]');
        q1.change(function(){ 
            if (q1.val() === q2.val() || q3.val() === q2.val() || q1.val() === q3.val()){
                alert('问题类型不能重复')
                q1.val('parents');
                q3.val('teacher');
                q3.val('lover');
            } else {
                $('option[value='+q1.val()+']').css('display', 'none');
            }

         });

        q2.change(function(){ 
            if (q1.val() === q2.val() || q3.val() === q2.val() || q1.val() === q3.val()){
                alert('问题类型不能重复')
                q1.val('parents');
                q3.val('teacher');
                q3.val('lover');
            } else {       
                $('option[value='+q2.val()+']').css('display', 'none');
            }
         });

        q3.change(function(){ 
            if (q1.val() === q2.val() || q3.val() === q2.val() || q1.val() === q3.val()){
                alert('问题类型不能重复')
                q1.val('parents');
                q3.val('teacher');
                q3.val('lover');
            } else {            
                $('option[value='+q3.val()+']').css('display', 'none');
            }
         });
    };

});