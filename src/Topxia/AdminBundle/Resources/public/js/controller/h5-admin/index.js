define(function(require, exports, module) {
    "use strict";
    exports.run = function() {
        $('.js-site-save').click(function() {
            let siteVal = $('input:radio:checked').val();
            if (siteVal == 2) {
                $('.js-nav-tab li').eq(0).addClass('active').siblings().removeClass('active');
                $('#find-page').addClass('active in').siblings().removeClass('active in');
            }
        })
        $('.js-site-set').click(function() {
            $('.js-nav-tab li').eq(1).addClass('active').siblings().removeClass('active');
        })
    }
});

