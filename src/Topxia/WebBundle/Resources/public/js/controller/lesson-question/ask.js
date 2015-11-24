define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('ckeditor');

    exports.run = function() {
        var validator = new Validator({
            element: '#lesson-question-plugin-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $.post($form.attr('action'), $form.serialize(), function(json) {
                   $("#modal").trigger("onAskQuestionSuccess",json);
                   $("#modal").modal('hide');
                }, 'json');
            }
        });

        validator.addItem({
            element: '#question_title',
            required: true
        });
        
        validator.addItem({
            element: '#question_content',
            required: true,
            display: '问题描述'
        });

        var editor = CKEDITOR.replace('question_content', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#question_content').data('imageUploadUrl')
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });

    };
});