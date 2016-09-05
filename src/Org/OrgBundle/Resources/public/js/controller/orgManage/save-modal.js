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
                window.location.reload();
            }).fail(function () {
                Notify.danger("添加组织机构失败，请重试！");
            });

        }
    });

    validator.addItem({
        element: '#org-name-field',
        required: true,
        rule: 'chinese_english byte_maxlength{max:30}',
    });

    validator.addItem({
        element: '#org-code-field',
        required: true,
        rule: 'alpha_numeric not_all_digital remote byte_maxlength{max:30}'
    });

    Validator.addRule("chinese_english", /^([\u4E00-\uFA29]|[a-zA-Z])*$/i, "{{display}}必须是中文字、英文字母组成");
    Validator.addRule("alpha_numeric", /^[a-zA-Z0-9]+$/i, "{{display}}必须是英文字母、数字组成");

    $modal.find('.delete-org').on('click', function () {
        if (!confirm('真的要删除该组织机构及其辖下组织机构吗？')) {
            return;
        }

        $.post($(this).data('url'), function (html) {
            $modal.modal('hide');
            window.location.reload();
        });

    });
});