define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var $table = $('#teacher-table');

        $table.on('click', '.cancel-promote-teacher', function() {
            if (!confirm(Translator.trans('真的要取消该教师推荐吗？'))) {
                return;
            }

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function(html) {
                Notify.success(Translator.trans('教师推荐已取消！'));
                var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            });

        });

    };

});