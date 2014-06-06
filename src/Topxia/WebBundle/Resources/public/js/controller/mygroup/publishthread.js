    define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var EditorFactory = require('common/kindeditor-factory');
  
    exports.run = function() {

        var editor = EditorFactory.create('#thread_content', 'simple', {extraFileUploadParams:{group:'user'}});
 		var validator = new Validator({
            element: '#user-thread-form',
            failSilently: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#group-save-btn').button('submiting').addClass('disabled');
            }
        });
        
        validator.addItem({
            element: '[name="thread[title]"]',
            required: true,
            rule: 'minlength{min:2} maxlength{max:200}',
            errormessageUrl: '长度为2-200位'
           
            
        });
        


       
    };

});

