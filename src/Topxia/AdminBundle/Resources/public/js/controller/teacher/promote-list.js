define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    exports.run = function(options) {

        var $table = $('#teacher-promote-table');

        $table.on('click', '.cancel-promote-teacher', function() {
            if (!confirm(Translator.trans('真的要取消该教师推荐吗？'))) {
                return;
            }

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function() {
                Notify.success(Translator.trans('教师推荐已取消！'));
                $tr.remove();
            });
        });
    };

});