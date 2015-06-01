define(function(require, exports, module) {

    var Swiper = require('swiper');

    exports.run = function() {
        var swiper = new Swiper('.aricle-carousel .swiper-container', {
            pagination: '.swiper-pager',
            swipeToPrev : false,
            swipeToNext : false,
            paginationClickable: true,
            autoplay: 3000,
            autoplayDisableOnInteraction: false,
            loop: true
        });
    }
    
});