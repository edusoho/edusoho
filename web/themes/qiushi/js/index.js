define(function(require, exports, module) {
    var Swiper = require('swiper');
    var Laryload = require('echo.js');
    exports.run = function() {
        Laryload.init();
        
        if ($(".es-poster .swiper-slide").length > 1) {
            var swiper = new Swiper('.es-poster.swiper-container', {
                pagination: '.swiper-pager',
                paginationClickable: true,
                autoplay: 5000,
                autoplayDisableOnInteraction: false,
                loop: true,
                calculateHeight: true,
                roundLengths: true,
                onInit: function(swiper) {
                   $(".swiper-slide").removeClass('swiper-hidden'); 
                }
            });
        }

        if ($(".qs-teacher-list .swiper-slide").length > 4) {
            var swiper = new Swiper('.qs-teacher-list .swiper-container', {
                // pagination: '.swiper-pager',
                // paginationClickable: true,
                // autoplay: 5000,
                autoplayDisableOnInteraction: false,
                loop: true,
                calculateHeight: true,
                roundLengths: true,
                slidesPerView: 4,
                onInit: function(swiper) {
                   $(".swiper-slide").removeClass('swiper-hidden'); 
                }
            });
            $('.qs-teacher-list .arrow-left').on('click', function(e){
                e.preventDefault();
                swiper.swipePrev();
            })
            $('.qs-teacher-list .arrow-right').on('click', function(e){
                e.preventDefault();
                swiper.swipeNext();
            })
        }

        $('#category-tab a:first').tab('show');
        $('#category-tab a').click(function(e){
            e.preventDefault()
            $(this).tab('show')
        })

        $('#live-category-tab a:first').tab('show');
        $('#live-category-tab a').click(function(e){
            e.preventDefault()
            $(this).tab('show')
        })
    }
});