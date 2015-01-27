define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var EditorFactory = require('common/kindeditor-factory');

    exports.run = function() {

        var editor_classroom = EditorFactory.create('#courseInstruction', 'simpleHaveEmoticons', {extraFileUploadParams:{group:'user'}});
        var validator = new Validator({
            element: '#classroom-set-form',
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#classroom-save').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="title"]',
            required: true
        });

        validator.on('formValidate', function(elemetn, event) {
            editor_classroom.sync();
        });
        
    };

});