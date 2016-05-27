define(function (require, exports, module) {
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
    require('common/validator-rules').inject(Validator);

    var $form = $('#org-manage-form');
    var $modal = $form.parents('.modal');
    var $table = $('#org-manage-table');
    var validator = new Validator({
        element: $form,
        autoSubmit: false,
        onFormValidated: function (error, results, $form) {
            if (error) {
                return;
            }
            $('#org-create-btn').button('submiting').addClass('disabled');

            $.post($form.attr('action'), $form.serialize(), function (html) {
                $modal.modal('hide');
                Notify.success('保存组织机构成功！');
            }).fail(function () {
                Notify.danger("添加组织机构失败，请重试！");
            });

        }
    });

    validator.addItem({
        element: '#org-name-field',
        required: true,
        rule: 'maxlength{max:100}'
    });

    validator.addItem({
        element: '#org-code-field',
        required: false,
        rule: 'alphanumeric not_all_digital remote'
    });

    $modal.find('.delete-org').on('click', function () {
        if (!confirm('真的要删除该组织机构及其辖下组织机构吗？')) {
            return;
        }

        $.post($(this).data('url'), function (html) {
            $modal.modal('hide');
        });

    });
});