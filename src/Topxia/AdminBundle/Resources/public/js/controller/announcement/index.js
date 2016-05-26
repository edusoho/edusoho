define(function(require, exports, module) {
    var SelectTree = require('edusoho.selecttree');

    exports.run = function() {
        if ($("#orgSelectTree").val()) {
            var selectTree = new SelectTree({
                element: "#orgSelectTree",
                name: 'orgCode'
            });
        }
        $('#announcement-table').on('click', '.delete-btn', function() {

            if (!confirm('确定删除此公告吗？')) {
                return;
            }

            $.post($(this).data('url'), function() {

                window.location.reload();

            });
        });

    };

});