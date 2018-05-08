import { isMobileDevice  } from 'common/utils';

let eventtype = isMobileDevice ? 'touchstart' : 'click';

let removeNavMobile = function () {
  $('.nav-mobile,.html-mask').removeClass('active');
  $('html,.es-wrap').removeClass('nav-active');
};

$('.js-navbar-more').click(function () {
  let $nav = $('.nav-mobile');

  if ($nav.hasClass('active')) {
    removeNavMobile();

  } else {
    let height = $(window).height();
    $nav.addClass('active').css('height', height);

    $('.html-mask').addClass('active');
    $('html,.es-wrap').addClass('nav-active');
  }
});

$('body').on(eventtype, '.html-mask.active', function () {
  removeNavMobile();
});