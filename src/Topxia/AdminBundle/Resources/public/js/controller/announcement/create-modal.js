define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    require('common/validator-rules').inject(Validator);
    require('es-ckeditor');
    exports.run = function() {
        var $modal = $('#announcement-create-form').parents('.modal');
        $form = $('#announcement-create-form');

        var validator = new Validator({
            element: '#announcement-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                $('#announcement-create-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    window.location.reload();
                }).error(function() {
                    Notify.danger(Translator.trans('admin.announcement.save_error_hint'));
                });
            }
        });

        var editor = CKEDITOR.replace('content-filed', {
            toolbar: 'SimpleMini'
        });

        validator.on('formValidate', function(elemetn, event) {
            editor.updateElement();
        });

        Validator.addRule(
            'time_check',
            /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/,
            Translator.trans('validate.valid_date_and_time_input.message')
        );

        validator.addItem({
            element: '[name=startTime]',
            required: true,
            rule: 'time_check deadline_date_check'
        });

        validator.addItem({
            element: '[name=content]',
            required: true,
            rule: 'minlength{min:2}'
        });

        validator.addItem({
            element: '[name=endTime]',
            required: true,
            rule: 'time_check date_check'
        });

        var url = $("#url").val();
        if (url){
            validator.addItem({
                element: '[name=url]',
                rule: 'url'
            });
        }

        var now = new Date();

        $("[name=startTime]").datetimepicker({
            autoclose: true,
            forceParse: true
        }).on('hide', function(ev) {
            validator.query('[name=startTime]').execute();
        });

        $('[name=startTime]').datetimepicker('setStartDate', now);

        $('[name=startTime]').datetimepicker().on('changeDate', function() {

            $('[name=endTime]').datetimepicker('setStartDate', $('[name=startTime]').val().substring(0, 16));
        });

        $("[name=endTime]").datetimepicker({
            autoclose: true,
            forceParse: true
        }).on('hide', function(ev) {
            validator.query('[name=endTime]').execute();
        });

        $('[name=endTime]').datetimepicker('setStartDate', now);

        $('[name=endTime]').datetimepicker().on('changeDate', function() {

            $('[name=startTime]').datetimepicker('setEndDate', $('[name=endTime]').val().substring(0, 16));
        });
    };

});