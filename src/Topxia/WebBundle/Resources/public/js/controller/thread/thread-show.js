define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('ckeditor');

    exports.run = function() {

        var editor = CKEDITOR.replace('post_content', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#post_content').data('imageUploadUrl')
        });

        var validator = new Validator({
            element: '#post-thread-form',
            failSilently: true,
            autoSubmit: false,
            onFormValidated: function(error) {
                if (error) {
                    return false;
                }
                $('#post-thread-btn').button('submiting').addClass('disabled');
            }
        });

        validator.addItem({
            element: '[name="content"]',
            required: true,
            rule: 'minlength{min:2} visible_character'
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });


        $('.thread-post-list').

    };
});