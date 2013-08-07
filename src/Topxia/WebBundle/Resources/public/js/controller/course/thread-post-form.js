define(function(require, exports, module) {

	require('ckeditor');
	var Validator = require('bootstrap.validator');

    exports.run = function() {

        CKEDITOR.replace('post_content', {
            height: '250px',
            forcePasteAsPlainText: true,
            toolbar: 'Simple',
            filebrowserUploadUrl: '/ckeditor/upload?group=course'
        });

        var validator = new Validator({
            element: '#thread-post-form',
        });

        validator.addItem({
            element: '[name="post[content]"]',
            required: true
        });

        Validator.query('#thread-post-form').on('formValidate', function(elemetn, event) {
            CKEDITOR.instances['post_content'].updateElement();
        });

    };

});