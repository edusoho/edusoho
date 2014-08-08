define(function(require, exports, module) {

    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require("jquery.bootstrap-datetimepicker");
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var $modal = $('#money-card-create-form').parents('.modal');

        var validator = new Validator({
            element: '#money-card-create-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }

                var btn = $("#batch-create");
                btn.button('loading');
                btn.addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function(html) {
                    $modal.modal('hide');
                    Notify.success('生成成功');
                    window.location.reload();
                }).error(function(){
                    Notify.danger('卡号生成失败，请重新生成！');
                });
            }
        });

        validator.addItem({
            element: '[name="money"]',
            required: true,
            rule: 'integer'
        })
        .addItem({
            element: '[name="cardLength"]',
            required: true,
            rule: 'integer'
        })
        .addItem({
            element: '[name="passwordLength"]',
            required: true,
            rule: 'integer'
        })
        .addItem({
            element: '[name="deadline"]',
            required: true,
            rule: ''
        })
        .addItem({
            element: '[name="number"]',
            required: true,
            rule: 'integer'
        })

        $("#deadlineCreate").datetimepicker({
            language: 'zh-CN',
            // autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });
    };

});