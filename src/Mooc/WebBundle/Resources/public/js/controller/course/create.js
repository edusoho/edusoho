define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('ckeditor');

    exports.run = function() {

        if($("#course-create-form").length>0) {

            var validator = new Validator({
                element: '#course-create-form',
                triggerType: 'change',
                onFormValidated: function(error){
                    if (error) {
                        return false;
                    }
                    $('#course-create-btn').button('submiting').addClass('disabled');
                }
            });

            validator.addItem({
                element: '[name="title"]',
                required: true
            });
        
        }

        var editor = CKEDITOR.replace('profile_about', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
        });

        var validator = new Validator({
            element: '#user-profile-form',
            failSilently: true,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#profile-save-btn').button('submiting').addClass('disabled');
            }
        });




    };

});