define(function(require, exports, module) {

	require('ckeditor');
	var Validator = require('bootstrap.validator');

    exports.run = function() {

        CKEDITOR.replace('post_content', {
            height: '160px',
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

        Validator.query('#thread-post-form').on('formValidated', function(err, msg, ele) {
            if (err == true) {
                return ;
            }
           
            var $form = $("#thread-post-form");
            $.post($form.attr('action'), $form.serialize(), function(html) {
                   window.location.reload();
            });

            return false;
        });

        $('[data-role=confirm-btn]').click(function(){
            var $btn = $(this);
            if (!confirm($btn.data('confirmMessage'))) {
                return false;
            }
            $.post($btn.data('url'), function(){
                var url = $btn.data('afterUrl');
                if (url) {
                    window.location.href = url;
                } else {
                    window.location.reload();
                }
            });
        });

        $(".thread-post-list").on('click', '[data-action=post-delete]', function() {
            if (!confirm("您真的要删除该回帖吗？")) {
                return false;
            }
            var $btn = $(this);
            $.post($btn.data('url'), function(){
                window.location.reload();
            });
        });

    };

});