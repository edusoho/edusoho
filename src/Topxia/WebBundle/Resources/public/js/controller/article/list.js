define(function(require, exports, module) {

    var Swiper = require('swiper');

    exports.run = function() {
        var swiper = new Swiper('.aricle-carousel .swiper-container', {
            pagination: '.swiper-pager',
            calculateHeight: true,
            paginationClickable: true,
            autoplay: 5000,
            autoplayDisableOnInteraction: false,
            loop: true
        });
    }
    
});