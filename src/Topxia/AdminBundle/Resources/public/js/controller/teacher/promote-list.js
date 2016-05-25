define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var SelectTree = require('edusoho.selecttree');

    exports.run = function(options) {
        if ($("#orgSelectTree").val()) {
            var selectTree = new SelectTree({
                element: "#orgSelectTree",
                name: 'orgCode'
            });
        }

        var $table = $('#teacher-promote-table');

        $table.on('click', '.cancel-promote-teacher', function() {
            if (!confirm('真的要取消该教师推荐吗？')) {
                return;
            }

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function() {
                Notify.success('教师推荐已取消！');
                $tr.remove();
            });
        });
    };

});