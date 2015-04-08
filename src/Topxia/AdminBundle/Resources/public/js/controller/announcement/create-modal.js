define(function(require, exports, module) {

    require("jquery.bootstrap-datetimepicker");
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var now = new Date();

        $("[name=startTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true
        });
        $('[name=startTime]').datetimepicker('setStartDate', now);

        $("[name=endTime]").datetimepicker({
            language: 'zh-CN',
            autoclose: true
        });
        $('[name=endTime]').datetimepicker('setStartDate', now);

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

                }).error(function(){
                    Notify.danger('操作失败');
                });
            }
        });

        validator.addItem({
            element: '[name=startTime]',
            required: true,
            rule: 'date_check'
        });

        validator.addItem({
            element: '[name=title]',
            required: true,
            rule: 'minlength{min:2}'
        });

        validator.addItem({
            element: '[name=url]',
            required: true,
            rule: 'url'
        });

        validator.addItem({
            element: '[name=endTime]',
            required: true,
            rule: 'date_check'
        });

    };

});