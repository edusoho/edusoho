define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    exports.run = function(options) {

        var $table = $('#teacher-promote-table');

        $table.on('click', '.cancel-promote-teacher', function() {
            if (!confirm(Translator.trans('admin.teacher.cancel_promote_hint'))) {
                return;
            }

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function() {
                Notify.success(Translator.trans('admin.teacher.cancel_recommend_success_hint'));
                $tr.remove();
            });
        });
    };

});