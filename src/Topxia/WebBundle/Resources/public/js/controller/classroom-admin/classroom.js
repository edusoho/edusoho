define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $table = $('#classroom-table');

        $table.on('click', '.close-classroom,.open-classroom,.cancel-recommend-classroom', function() {
            var $trigger = $(this);
            if (!confirm($trigger.attr('title') + Translator.trans('吗？'))) {
                return;
            }
            $.post($(this).data('url'), function(html) {
                Notify.success($trigger.attr('title') + Translator.trans('成功！'));
                var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function() {
                Notify.danger($trigger.attr('title') + Translator.trans('失败'));
            });

        });


        $('.delete-classroom').on('click', function() {
            if (!confirm(Translator.trans('真的要删除该班级吗？'))) {
                return;
            }
            $.post($(this).data('url'), function() {
                Notify.success(Translator.trans('删除成功！'));
                window.location.reload();
            });
        });

    }

});