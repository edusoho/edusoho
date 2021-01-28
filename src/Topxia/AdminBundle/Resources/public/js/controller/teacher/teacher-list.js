define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $table = $('#teacher-table');

        $table.on('click', '.cancel-promote-teacher', function() {
            if (!confirm(Translator.trans('admin.teacher.cancel_promote_hint'))) {
                return;
            }

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function(html) {
                Notify.success(Translator.trans('admin.teacher.cancel_recommend_success_hint'));
                var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            });

        });

    };

});