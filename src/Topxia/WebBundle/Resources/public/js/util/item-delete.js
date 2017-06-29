define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

    module.exports = function($element, onSuccess) {
        $element.on('click', '[data-role=item-delete]', function() {
            var $btn = $(this),
                name = $btn.data('name'),
                message = $btn.data('message');

            if (!message) {
                message = Translator.trans('真的要删除该') + name + Translator.trans('吗？');
            }

            if (!confirm(message)) {
                return ;
            }

            $.post($btn.data('url'), function() {
                if ($.isFunction(onSuccess)) {
                    onSuccess.call($element, $item);
                } else {
                    $btn.parents('[data-role=item]').remove();
                    Notify.success(Translator.trans('删除') + name + Translator.trans('成功'));
                }
            });

        });


    };

});