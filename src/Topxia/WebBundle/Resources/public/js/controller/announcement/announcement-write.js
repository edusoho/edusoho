define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('jquery.bootstrap-datetimepicker');
    require('ckeditor');

    exports.run = function() {

        require("../../controller/announcement/announcement-manage").run();

        var validator = new Validator({
            element: '#announcement-write-form',
            autoSubmit: false
        });

        validator.addItem({
            element: '#announcement-content-field',
            required: true
        });
        
        var now = new Date();

        $("[name=startTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true
        }).on('hide', function(ev){
            validator.query('[name=startTime]').execute();
        });

        $('[name=startTime]').datetimepicker('setStartDate', now);

        $("[name=endTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true
        }).on('hide', function(ev){
            validator.query('[name=endTime]').execute();
        });

        $('[name=endTime]').datetimepicker('setStartDate', now);

        // group: 'course'
        var editor = CKEDITOR.replace('announcement-content-field', {
            toolbar: 'Simple',
            filebrowserImageUploadUrl: $('#announcement-content-field').data('imageUploadUrl')
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });

        validator.on('formValidated', function(error, msg, $form) {
            if (error) {
                return;
            }
            $.post($form.attr('action'), $form.serialize(), function(json) {
                window.location.reload();
            }, 'json');

        });

        $('#modal').modal('show');

    };
});