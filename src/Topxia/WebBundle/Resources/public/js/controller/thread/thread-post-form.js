define(function(require, exports, module) {

    require('ckeditor');
	var Validator = require('bootstrap.validator');

    exports.run = function() {

        var editor = CKEDITOR.replace('post-content-field', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#post-content-field').data('imageUploadUrl'),
            height: 300
        });

        var validator = new Validator({
            element: '#post-thread-form',
        });

        validator.addItem({
            element: '#post-content-field',
            required: true
        });

        Validator.query('#post-thread-form').on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });

    };

});