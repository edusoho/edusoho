import Swiper from 'swiper';
import '../teacher/follow-btn';
import Notification from 'app/js/notice-comp/notice';
import { isMobileUpdateDevice } from 'common/utils';

if ($('.es-poster .swiper-slide').length > 1) {
  var swiper = new Swiper('.es-poster.swiper-container', {
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

$('body').on('click', '.js-course-filter', function () {
  var $btn = $(this);
  var courseType = $btn.data('type');
  var text = $('.course-filter .visible-xs .active a').text();
  $.get($btn.data('url'), function (html) {
    $('#' + courseType + '-list-section').after(html).remove();
    var parent = $btn.parent();
    if (!parent.hasClass('course-sort')) {
      text = $btn.find('a').text();
    }
    $('.course-filter .visible-xs .btn').html(text + ' ' + '<span class="caret"></span>');
    echo.init();
  });
});



$(document).ready(function() {
  if (isMobileUpdateDevice()) return;
  if (!$('.js-current-live-course').length) return;
  const $currentLiveCourse = $('.js-current-live-course');
  const courseInfo = {
    title: $currentLiveCourse.data('title'),
    link: $currentLiveCourse.data('url'),
    liveStatus: 'wait',
    url: $currentLiveCourse.data('src'),
    time: $currentLiveCourse.data('time')
  };
  
  const flag = (courseInfo.liveStatus === 'ing');
  const courseStatus = flag ?
    '<div class="notification-live-info__ing">正在直播中<i class="es-icon es-icon-entry-live cd-ml8"></i></div>':
    `<div class="notification-live-info__ing start"><span class="live-time">${courseInfo.time}</span><span class="live-divider">|</span></span><span class="color-success">即将开始</span></div>`;
  new Notification({
    positionClass: $currentLiveCourse.data('position'),
    title: '<div><i class="es-icon es-icon-entry-live cd-mr8"></i>直播课程提醒</div>',
    template: `
      <div class="clearfix notification-live-item">
        <div class="item-one"><a href="${courseInfo.link}" target="_blank"><img src="${courseInfo.url}" alt="course-cover" class="img-responsive"></a></div>
        <div class="notification-live-info item-one">
          <a class="notification-live-info__title text-overflow" href="${courseInfo.link}" target="_blank" data-toggle="tooltip" data-placement="top" title="${courseInfo.title}">${courseInfo.title}</a>
          ${courseStatus}
          <div class="notification-live-info__link bg-primary"><a href="${courseInfo.link}" target="_blank">进入教室</a></div>
        </div>
      </div>`,
  });
});
