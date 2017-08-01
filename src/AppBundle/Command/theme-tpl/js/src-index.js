import Swiper from 'swiper';
var mySwiper = new Swiper ('.swiper-container', {
    loop: true,
    onInit: function(swiper) {
        $(".swiper-slide").removeClass('swiper-hidden');
    }
});