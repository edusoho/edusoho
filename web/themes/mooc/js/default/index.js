define(function(require, exports, module) {
    require("jquery.waypoints");
    var Lazyload = require('echo.js');

    var Swiper = require('swiper');
    exports.run = function() {
      var bannerSwiper = new Swiper('.es-poster.swiper-container', {
            pagination: '.es-poster .swiper-pager',
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
       $('.es-poster .arrow-left').on('click', function(e){
            e.preventDefault()
            bannerSwiper.swipePrev()
           })
       $('.es-poster .arrow-right').on('click', function(e){
            e.preventDefault()
            bannerSwiper.swipeNext()
        })
        var setSwiper = new Swiper('.set-right.swiper-container', {
            autoplay: 6000,
            autoplayDisableOnInteraction: false,
            calculateHeight: true,
            roundLengths: true,
            loop: true,
            slidesPerView: 4,
            onInit: function(swiper) {
               $(".swiper-slide").removeClass('swiper-hidden'); 
            }
        });
        $('.set-right .arrow-left').on('click', function(e){
            e.preventDefault()
            setSwiper.swipePrev()
           })
       $('.set-right .arrow-right').on('click', function(e){
            e.preventDefault()
            setSwiper.swipeNext()
        })
        var classSwiper = new Swiper('.class-list.swiper-container', {
            calculateHeight: true,
            roundLengths: true,
            slidesPerView: 4,
            onInit: function(swiper) {
               $(".swiper-slide").removeClass('swiper-hidden'); 
            }
        });
        $('.class-list .arrow-left').on('click', function(e){
            e.preventDefault()
            classSwiper.swipePrev()
           })
       $('.class-list .arrow-right').on('click', function(e){
            e.preventDefault()
            classSwiper.swipeNext()
        })
        Lazyload.init();
         var teachCarousel = function() {
            var $this = $(".mooc-teacher .carousel-inner .item");
            for (var i = 0; i < $this.length; i++) {
              if (i == 0) {
                var html = '<li data-target=".carousel" data-slide-to="0" class="active"></li>';
                $this.parents(".teach-list").siblings(".carousel-indicators").append(html);
              }else {
                var html = '<li data-target=".carousel" data-slide-to="'+i+'"></li>';
                $this.parents(".teach-list").siblings(".carousel-indicators").append(html);
              }
            }
         }();
         var indexAnimate = function () {
              $(".js-animate-item").waypoint(function(){
                $(this).addClass('active');
              },{offset:500});
        }();
        var swiperLength = function (obj,parentsObj) {
           if($(obj).length<=4){
            $(obj).parents(parentsObj).siblings().remove();
           }
         }
         swiperLength('.set-right .swiper-slide','.set-right .swiper-wrapper');
         swiperLength('.class-list .swiper-slide','.class-list .swiper-wrapper');

    };
});