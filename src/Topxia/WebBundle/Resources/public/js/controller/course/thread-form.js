define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('ckeditor');

    exports.run = function() {
        require('./common').run();

        // group: 'default'
        var editor = CKEDITOR.replace('thread_content', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#thread_content').data('imageUploadUrl')
        });

        var validator = new Validator({
            element: '#thread-form'
        });

        validator.addItem({
            element: '[name="thread[title]"]',
            required: true,
            rule:'visible_character'
        });

        validator.addItem({
            element: '[name="thread[content]"]',
            required: true
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });

        validator.on('formValidated', function(err, msg, $form) {
            if (err == true) {
                return ;
            }

            $form.find('[type=submit]').attr('disabled', 'disabled');

            return true;
        });

    };

});