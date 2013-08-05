define(function(require, exports, module) {
	require('ckeditor');
    var Validator = require('bootstrap.validator');

    exports.run = function() {

        var validator = new Validator({
            element: '#announcement-write-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '[name="announcement[content]"]',
            required: true
        });

        CKEDITOR.replace('announcement_content', {
            height: 300,
            resize_enabled: false,
            forcePasteAsPlainText: true,
            toolbar: 'Mini',
            filebrowserUploadUrl: '/ckeditor/upload?group=course'
        });

        validator.on('formValidate', function(elemetn, event) {
            CKEDITOR.instances['announcement_content'].updateElement();
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }
            console.log($form.attr,$form.serialize);
            $.post($form.attr('action'), $form.serialize(), function(json) {
                window.location.reload();
            }, 'json');

        });

        $('#modal').modal('show');

    };
});