define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
	require('es-ckeditor');

    function getEditorContent(editor){
        editor.updateElement();
        var z = editor.getData();
        var x = editor.getData().match(/<embed[\s\S]*?\/>/g);
        if (x) {
            for (var i = x.length - 1; i >= 0; i--) {
               var y = x[i].replace(/\/>/g,"wmode='Opaque' \/>");
               var z =  z.replace(x[i],y);
            };
        }
        return z;
    }

    exports.run = function() {

    	var editor = CKEDITOR.replace('text-content-field', {
            toolbar: 'Full',
            filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
            filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
            allowedContent: true,
            height: 300
        });


    	var validator = new Validator({
            element: '#task-create-form',
            autoSubmit: false,
            onFormValidated: function(error) {
                if (error) {
                    return false;
                }
                var $form = $('#task-create-form');
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $('#modal').modal('hide');
                    var $tr = $(html);
                    Notify.success(Translator.trans('任务添加成功!'));
                });
            }
        });

        validator.addItem({
            element: '[name="title"]',
            required: true,
            rule: 'minlength{min:2} maxlength{max:100}',
            errormessageUrl: Translator.trans('长度为2-100位')
        });

        validator.addItem({
            element: '[name="content"]',
            required: true
        });

        validator.on('formValidate', function(elemetn, event) {
            var content = getEditorContent(editor);
            $("#text-content-field").val(content);
        });
    };

});
