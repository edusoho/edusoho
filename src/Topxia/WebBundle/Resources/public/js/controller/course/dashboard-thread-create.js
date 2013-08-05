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
            element: '#thread-create-form',
        });

        validator.addItem({
            element: '[name="thread[title]"]',
            required: true
        });

        validator.addItem({
            element: '[name="thread[content]"]',
            required: true
        });

        Validator.query('#thread-create-form').on('formValidate', function(elemetn, event) {
            CKEDITOR.instances['thread_content'].updateElement();
        });

        Validator.query('#thread-create-form').on('formValidated', function(err, msg, ele) {
            if (err == true) {
                return ;
            }

            var $form = $("#thread-create-form");
            $.post($form.attr('action'), $form.serialize(), function(thread) {
                window.location.href = thread.link;
            }, 'json');
            return false;
        });



    };

});