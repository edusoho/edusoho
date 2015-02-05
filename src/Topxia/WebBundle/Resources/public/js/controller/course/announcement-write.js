define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('ckeditor');

    exports.run = function() {

        var validator = new Validator({
            element: '#announcement-write-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '#announcement-content-field',
            required: true
        });

        // group: 'course'
        CKEDITOR.replace('announcement-content-field', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#announcement-content-field').data('imageUploadUrl')
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