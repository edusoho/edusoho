define(function(require, exports, module) {
    "use strict";
    exports.run = function() {
        $('.js-tooltip-twig-widget').find('.js-twig-widget-tips').each(function () {
            var $self = $(this);
            $self.popover({
                html: true,
                trigger: 'hover',//'hover','click'
                placement: $self.data('placement'),//'bottom',
                content: $self.next(".js-twig-widget-html").html()
            });
        });
    }
});

