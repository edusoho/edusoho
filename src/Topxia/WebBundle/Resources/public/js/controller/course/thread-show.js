define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('ckeditor');

    exports.run = function() {
        require('./common').run();

        // group: 'course'
        var editor =  CKEDITOR.replace('post_content', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#post_content').data('imageUploadUrl')
        });

        var validator = new Validator({
            element: '#thread-post-form'
        });

        validator.addItem({
            element: '[name="post[content]"]',
            required: true
        });

        Validator.query('#thread-post-form').on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });

        Validator.query('#thread-post-form').on('formValidated', function(err, msg, ele) {
            if (err == true) {
                return ;
            }
            
            $('.thread-post-list').find('li.empty').remove();
            var $form = $("#thread-post-form");

            $form.find('[type=submit]').attr('disabled', 'disabled');
            $.post($form.attr('action'), $form.serialize(), function(html) {
                $("#thread-post-num").text(parseInt($("#thread-post-num").text()) + 1);
                var id = $(html).appendTo('.thread-post-list').attr('id');
                editor.setData('');

                $form.find('[type=submit]').removeAttr('disabled');

                window.location.href = '#' + id;
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

        $('.thread-post-list').on('click','.thread-post-action',function(){

            var userName=$(this).data('user');
         
            editor.focus();
            editor.insertHtml('@'+userName+'&nbsp;');
            
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