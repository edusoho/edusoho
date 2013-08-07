define(function(require, exports, module) {

	require('ckeditor');
	var Validator = require('bootstrap.validator');

    exports.run = function() {
        require('./common').run();

    	CKEDITOR.replace('thread_content', {
            height: '360px',
            forcePasteAsPlainText: true,
            toolbar: 'Simple',
            filebrowserUploadUrl: '/ckeditor/upload?group=course'
        });

        var validator = new Validator({
            element: '#thread-form',
        });

        validator.addItem({
            element: '[name="thread[title]"]',
            required: true
        });

        validator.addItem({
            element: '[name="thread[content]"]',
            required: true
        });

        validator.on('formValidate', function(elemetn, event) {
            CKEDITOR.instances['thread_content'].updateElement();
        });

    };

});