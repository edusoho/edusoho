define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('.carts-popover-list').on('click','.carts-item-delete',function(){
            $btn = $(this);
            $.get($btn.data('url'),function(result){
                if(result.status == 'success') {
                    $btn.parents('li').remove();
                    var count = Number($('#float-carts #carts-badge').html());
                    count == 1 && $('.btn-carts .btn').popover('hide');
                    count > 0 && $('#float-carts #carts-badge').html(--count);
                }
         
            });
        });
    }
});