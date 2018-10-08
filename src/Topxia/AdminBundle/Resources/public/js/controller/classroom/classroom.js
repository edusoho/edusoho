define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    exports.run = function() {
        var $table = $('#classroom-table');

        $table.on('click', '.close-classroom,.open-classroom,.cancel-recommend-classroom', function() {
            var $trigger = $(this);
            if (!confirm($trigger.attr('title') + Translator.trans('admin.classroom.operation_hint'))) {
                return;
            }
            $.post($(this).data('url'), function(html) {
                Notify.success($trigger.attr('title') + Translator.trans('admin.classroom.operation_success_hint'));
                var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function() {
                Notify.danger($trigger.attr('title') + Translator.trans('admin.classroom.operation_fail_hint'));
            });

        });


        $('.delete-classroom').on('click', function() {
            if (!confirm(Translator.trans('admin.classroom.delete_hint'))) {
                return;
            }
            $.post($(this).data('url'), function() {
                Notify.success(Translator.trans('admin.classroom.delete_success_hint'));
                window.location.reload();
            });
        });

    }

});