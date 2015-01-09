define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
                    console.log(Number($('#count').html()));
        $('.carts-popover-list').on('click','.carts-item-delete',function(){
            $btn = $(this);
            $.get($btn.data('url'),function(result){
                if(result.status == 'success') {
                    var count = Number($('#count').html());
                    count == 1 && $('.btn-carts .btn').popover('hide');
                    count > 0 && $('#count, #carts-badge').html(--count);

                    var price = Number($('#price').html());
                    price = $('#price').html(price - Number($btn.prev('div').find('.course-price').html()));

                    $btn.parents('li').remove();
                }
            });
        });
    }
});