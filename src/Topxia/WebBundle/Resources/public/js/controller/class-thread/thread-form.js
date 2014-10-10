define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');

    exports.run = function() {

        var editor = EditorFactory.create('#thread-content-field', 'simple', {extraFileUploadParams:{group:'course'}});

        var validator = new Validator({
            element: '#thread-form'
        });

        validator.addItem({
            element: '#thread-title-field',
            required: true,
            errormessage: "请输入标题"
        });

        validator.addItem({
            element: '#thread-content-field',
            required: true,
            errormessage: "请输入内容"
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });

        validator.on('formValidated', function(err, msg, $form) {
            if (err == true) {
                return ;
            }

            $form.find('[type=submit]').attr('disabled', 'disabled');

            return true;
        });

    };

});