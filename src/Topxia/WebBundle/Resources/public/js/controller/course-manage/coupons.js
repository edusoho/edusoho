define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        require('./header').run();

        var $list = $("#course-coupon-list");

        $list.on('click', '.coupon-remove', function(){
            var $tr = $(this).parents('tr');
            if (!confirm('您真的要删除该优惠码？')) {
                return ;
            }

            $.post($(this).data('url'), function(){
                Notify.success('删除优惠码成功！');
                $tr.remove();
            }).error(function(){
                Notify.danger('删除优惠码失败，请重试！');
            });
        });
    }

});