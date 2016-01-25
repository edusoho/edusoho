define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');

    require('common/validator-rules').inject(Validator);

    require('jquery.bootstrap-datetimepicker');
    require('es-ckeditor');

    Validator.addRule(
        'time_check',
    /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/,
    '请输入正确的日期和时间,格式如XXXX-MM-DD hh:mm'
    );

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
        
        validator.addItem({
            element: '[name=startTime]',
            required: true,
            rule: 'time_check'
        });

        validator.addItem({
            element: '[name=endTime]',
            required: true,
            rule: 'time_check date_check'
        });

        var now = new Date();

        $("[name=startTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true
        }).on('hide', function(ev){
            validator.query('[name=startTime]').execute();
        });

        $('[name=startTime]').datetimepicker('setStartDate', now);

        $('[name=startTime]').datetimepicker().on('changeDate',function(){
            $('[name=endTime]').datetimepicker('setStartDate',$('[name=startTime]').val().substring(0,16));
        });

        $("[name=endTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true
        }).on('hide', function(ev){
            validator.query('[name=endTime]').execute();
        });

        $('[name=endTime]').datetimepicker('setStartDate', now);

        $('[name=endTime]').datetimepicker().on('changeDate',function(){
            $('[name=startTime]').datetimepicker('setEndDate',$('[name=endTime]').val().substring(0,16));
        });

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