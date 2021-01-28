define(function(require, exports, module) {

var Notify = require('common/bootstrap-notify');

    exports.run = function(options) {
        var $element = $('#coupon-list');
        $element.on('click', '.short-text', function() {
            var $short = $(this);
            $short.slideUp('fast').parents('.short-long-text').find('.long-text').slideDown('fast');
        });

      $element.on('click', '.long-text', function() {
        var $long = $(this);
        $long.slideUp('fast').parents('.short-long-text').find('.short-text').slideDown('fast');
      });

        $('#coupon-list').on('click', 'a.coupon-remove', function() {
            if (!confirm(Translator.trans('confirm.remove_coupon'))) return false;
            var $btn = $(this);

            var $tr = $(this).parents('tr');
            $.post($(this).data('url'), function(response){
                if (response == true) {
                    $tr.remove();
                    Notify.success(Translator.trans('notify.remove_coupon_success'));
                } else {
                    Notify.warning(Translator.trans('notify.remove_coupon_fail'));
                }
            }, 'json');

        });

    };

});
