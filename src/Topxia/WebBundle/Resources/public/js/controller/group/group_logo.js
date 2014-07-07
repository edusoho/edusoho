 define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var EditorFactory = require('common/kindeditor-factory');

    exports.run = function() {
        if($('#group-avatar-form').length>0){
            var validator = new Validator({
            element: '#group-avatar-form'
            });

            validator.addItem({
            element: '[name="form[avatar]"]',
            required: true,
            rule: 'maxsize_image',
            requiredErrorMessage: '请选择要上传的文件。'
            });

        }
    
        if($('#group_about').length>0){
            console.log(1);
            var editor = EditorFactory.create('#group_about', 'simpleHaveEmoticons', {extraFileUploadParams:{group:'user'}});
            var validator = new Validator({
            element: '#user-group-form',
            failSilently: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#group-save-btn').button('submiting').addClass('disabled');
            }
        });
        
        validator.addItem({
            element: '[name="group[grouptitle]"]',
            required: true,
            rule: 'minlength{min:2} maxlength{max:12}',
            errormessageUrl: '长度为2-12位'
           
            
        });

           }
    };

});

