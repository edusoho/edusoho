define(function(require, exports, module) {

    exports.run = function() {
        var ztree = require('edusoho.ztree');
        ztree('#orgZtree', "#orgName", "#orgCode");
        $('#announcement-table').on('click','.delete-btn',function(){

            if (!confirm('确定删除此公告吗？')) {
                return ;
            }

            $.post($(this).data('url'),function(){

                window.location.reload();

            });
        });

    };

});