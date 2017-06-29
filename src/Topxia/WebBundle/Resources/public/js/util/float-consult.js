define(function(require, exports, module) {
    exports.run = function () {
        var $element = $('#float-consult');
        if ($element.length == 0) {
            return ;
        }

        if ($element.data('display') == 'off') {
            return ;
        }

        var marginTop = (0 - $element.height() / 2) + 'px' ;

        var isIE10 = /MSIE\s+10.0/i.test(navigator.userAgent)
        && (function() {"use strict";return this === undefined;}());

        var isIE11 = (/Trident\/7\./).test(navigator.userAgent);

        if (isIE10 || isIE11) {
            $element.css( {marginTop: marginTop, visibility: 'visible',marginRight:'16px'});
        } else {
            $element.css( {marginTop: marginTop, visibility: 'visible'});
        }

        $element.find('.btn-group-vertical .btn').popover({
            placement: 'left',
            trigger: 'hover',
            html: true,
            content: function() {
                return $($(this).data('contentElement')).html();
            }
        });
    }
})