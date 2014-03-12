define(function(require, exports, module) {

var Notify = require('common/bootstrap-notify');

    exports.run = function(options) {

        $('#coupon-list').on('click', 'a.coupon-remove', function() {
            if (!confirm('确认要删除此优惠卷？')) return false;
            var $btn = $(this);

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function(response){
                if (response == true) {
                    $tr.remove();
                    Notify.success('删除成功!');
                } else {
                    Notify.warning('删除失败!');
                }
            }, 'json');

        });
      
    };

});
