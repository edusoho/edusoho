import Swiper from 'swiper';
var mySwiper = new Swiper ('.swiper-container', {
    loop: true,
    autoplay : 3000,
    onInit: function(swiper) {
        $(".swiper-slide").removeClass('swiper-hidden');
    }
});