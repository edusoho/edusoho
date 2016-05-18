define(function(require, exports, module) {

    exports.run = function() {
       var SelectZtree = require('edusoho.selectztree');
        var selectTree = new SelectZtree({
            ztreeDom: '#orgZtree',
            clickDom: "#orgName",
            valueDom: "#orgCode"
        });
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