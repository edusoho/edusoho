define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    exports.run = function() {

     

        $('.photo-manage-item-del').on('click', function() {
            if (!confirm('真的要删除该图片吗？')) {
                return ;
            }

            var $tr = $(this).parents('li');
            $.post($(this).data('url'), function(){
                $tr.remove();
            });

        });

    };

});