define(function(require, exports, module) {

    require('ckeditor');
	var Validator = require('bootstrap.validator');

    exports.run = function() {

        var editor = CKEDITOR.replace('post_content', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#post_content').data('imageUploadUrl'),
            height: 300
        });

        var validator = new Validator({
            element: '#post-thread-form',
        });

        validator.addItem({
            element: '[name="post[content]"]',
            required: true
        });

        Validator.query('#post-thread-form').on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });

    };

});