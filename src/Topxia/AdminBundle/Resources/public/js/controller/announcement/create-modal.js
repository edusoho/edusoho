define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    require('common/validator-rules').inject(Validator);
    exports.run = function() {
        require('orgbundle/controller/org/org-tree-select').run();
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
                    Notify.danger('操作失败');
                });
            }
        });

        Validator.addRule(
            'time_check',
            /^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29) ([0-1]{1}[0-9]{1})|(2[0-4]{1}):[0-5]{1}[0-9]{1}$/,
            '请输入正确的日期和时间,格式如XXXX-MM-DD hh:mm'
        );

        validator.addItem({
            element: '[name=startTime]',
            required: true,
            rule: 'time_check'
        });

        validator.addItem({
            element: '[name=content]',
            required: true,
            rule: 'minlength{min:2}'
        });

        validator.addItem({
            element: '[name=url]',
            rule: 'url'
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
        }).on('hide', function(ev) {
            validator.query('[name=startTime]').execute();
        });

        $('[name=startTime]').datetimepicker('setStartDate', now);

        $('[name=startTime]').datetimepicker().on('changeDate', function() {

            $('[name=endTime]').datetimepicker('setStartDate', $('[name=startTime]').val().substring(0, 16));
        });

        $("[name=endTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true
        }).on('hide', function(ev) {
            validator.query('[name=endTime]').execute();
        });

        $('[name=endTime]').datetimepicker('setStartDate', now);

        $('[name=endTime]').datetimepicker().on('changeDate', function() {

            $('[name=startTime]').datetimepicker('setEndDate', $('[name=endTime]').val().substring(0, 16));
        });

    };

});