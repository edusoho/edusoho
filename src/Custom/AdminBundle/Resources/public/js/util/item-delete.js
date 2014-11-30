define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

    module.exports = function($element, onSuccess) {
        $element.on('click', '[data-role=item-delete]', function() {
            var $btn = $(this),
                name = $btn.data('name'),
                message = $btn.data('message');

            if (!message) {
                message = '真的要删除该' + name + '吗？';
            }

            if (!confirm(message)) {
                return ;
            }

            $.post($btn.data('url'), function() {
                if ($.isFunction(onSuccess)) {
                    onSuccess.call($element, $item);
                } else {
                    $btn.parents('[data-role=item]').remove();
                    Notify.success('删除' + name + '成功');
                }
            });

        });


    };

});