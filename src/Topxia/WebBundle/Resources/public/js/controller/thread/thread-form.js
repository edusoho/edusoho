define(function(require, exports, module) {

       require('ckeditor');
    var Validator = require('bootstrap.validator');
    var Share=require('../../util/share.js');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
                Share.create({
                selector: '.share',
                icons: 'itemsAll',
                display: ''
        });

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