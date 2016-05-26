define(function(require, exports, module) {
    "use strict";

    var Notify = require('common/bootstrap-notify');
    var SelectTree = require('edusoho.selecttree');

    exports.run = function() {
        if ($("#orgSelectTree").val()) {
            var selectTree = new SelectTree({
                element: "#modalOrgSelectTree",
                name: 'orgCode',
                modal: true
            });
        }

        var Validator = require('bootstrap.validator');
        require('common/validator-rules').inject(Validator);

        var validator = new Validator({
            element: '#user-edit-form',
            autoSubmit: false,
            failSilently: true,
            onFormValidated: function(error, results, $form) {
                if (error) {
                    return false;
                }
                $('#edit-user-btn').button('submiting').addClass('disabled');

                $.post($form.attr('action'), $form.serialize(), function() {
                    Notify.success('修改用户所属机构成功');
                    window.location.reload();
                }).error(function() {
                    Notify.danger('操作失败');
                });
            }
        });
    }
});