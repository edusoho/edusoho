define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {

        var $table = $('#photo-table');


        $table.on('click', '.delete-course', function() {
            if (!confirm('真的要删除该图片吗？')) {
                return ;
            }

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function(){
                $tr.remove();
            });

        });

    };

});