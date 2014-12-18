define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('.carts-popover-list').on('click','.carts-item-delete',function(){
            $btn = $(this);
            $.get($btn.data('url'),function(){
                Notify.success('删除成功');
                $btn.parents('li').remove();
            });
        });
    }
});