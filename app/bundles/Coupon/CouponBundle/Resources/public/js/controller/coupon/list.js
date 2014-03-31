define(function(require, exports, module) {

var Notify = require('common/bootstrap-notify');

    exports.run = function(options) {
        var $element = $('#coupon-list');
        $element.on('click', '.short-text', function() {
            var $short = $(this);
            $short.slideUp('fast').parents('.short-long-text').find('.long-text').slideDown('fast');
        });

        $('#coupon-list').on('click', 'a.coupon-remove', function() {
            if (!confirm('确认要删除此批次优惠码？')) return false;
            var $btn = $(this);

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function(response){
                if (response == true) {
                    $tr.remove();
                    Notify.success('删除优惠码成功!');
                } else {
                    Notify.warning('删除优惠码失败!');
                }
            }, 'json');

        });

    };

});
