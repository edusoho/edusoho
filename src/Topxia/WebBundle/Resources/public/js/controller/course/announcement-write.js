define(function(require, exports, module) {
    var EditorFactory = require('common/kindeditor-factory');
    var Validator = require('bootstrap.validator');

    exports.run = function() {

        var validator = new Validator({
            element: '#announcement-write-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '#announcement-content-field',
            required: true
        });

        var editor = EditorFactory.create('#announcement-content-field', 'simple', {extraFileUploadParams:{group:'course'}});

        validator.on('formValidate', function(elemetn, event) {
            editor.sync();
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }
            $.post($form.attr('action'), $form.serialize(), function(json) {
                window.location.reload();
            }, 'json');

        });

        $('#modal').modal('show');

    };
});