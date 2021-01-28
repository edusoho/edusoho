import 'jquery.cycle2';
import Swiper from 'swiper';

if ($('.es-live-poster .swiper-slide').length > 1) {
  var swiper = new Swiper('.es-live-poster.swiper-container', {
    pagination: '.swiper-pager',
    paginationClickable: true,
    autoplay: 5000,
    autoplayDisableOnInteraction: false,
    loop: true,
    calculateHeight: true,
    roundLengths: true,
    onInit: function (swiper) {
      $('.swiper-slide').removeClass('swiper-hidden');
    }
  });
}

$('.homepage-feature').cycle({
  fx: 'scrollHorz',
  slides: '> a, > img',
  log: 'false',
  pauseOnHover: 'true'
});

$('.live-rating-course').find('.media-body').hover(function () {
  $(this).find('.rating').show();
}, function () {
  $(this).find('.rating').hide();
});

