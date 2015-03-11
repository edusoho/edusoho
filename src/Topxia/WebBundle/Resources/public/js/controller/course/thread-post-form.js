define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    require('ckeditor');

    exports.run = function() {

        // group: 'course'
        var editor = CKEDITOR.replace('post_content', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#post_content').data('imageUploadUrl'),
            height: 300
        });

        var validator = new Validator({
            element: '#thread-post-form',
        });

        validator.addItem({
            element: '[name="post[content]"]',
            required: true
        });

        Validator.query('#thread-post-form').on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });

    };

});