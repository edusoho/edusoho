import { chapterAnimate } from 'app/common/widget/chapter-animate';
import AttachmentActions from 'app/js/attachment/widget/attachment-actions';
import { Browser } from 'common/utils';
import { buyBtn } from 'app/common/widget/btn-util';

initTaskLearnChart();
triggerMemberExpired();
remainTime();

if ($('.js-attachment-list').length > 0) {
  new AttachmentActions($('.js-attachment-list'));
}

buyBtn($('.js-buy-btn'));
buyBtn($('.js-task-buy-btn'));

function initTaskLearnChart() {
  let colorPrimary = $('.color-primary').css('color');
  let colorWarning = $('.color-warning').css('color');
  $('#freeprogress').easyPieChart({
    easing: 'easeOutBounce',
    trackColor: '#ebebeb',
    barColor: colorPrimary,
    scaleColor: false,
    lineWidth: 14,
    size: 145,
    onStep: function(from, to, percent) {
      $('canvas').css('height', '146px');
      $('canvas').css('width', '146px');
      if (Math.round(percent) == 100) {
        $(this.el).addClass('done');
      }
      $(this.el).find('.percent').html(Translator.trans('course_set.learn_progress') + '<br><span class="num">' + Math.round(percent) + '%</span>');
    }
  });

  $('#orderprogress-plan').easyPieChart({
    easing: 'easeOutBounce',
    trackColor: '#ebebeb',
    barColor: colorWarning,
    scaleColor: false,
    lineWidth: 14,
    size: 145,
  });

  let bg = $('#orderprogress-plan').length > 0 ? 'transparent' : '#ebebeb';

  $('#orderprogress').easyPieChart({
    easing: 'easeOutBounce',
    trackColor: bg,
    barColor: colorPrimary,
    scaleColor: false,
    lineWidth: 14,
    size: 145,
    onStep: function(from, to, percent) {
      if (Math.round(percent) == 100) {
        $(this.el).addClass('done');
      }
      $(this.el).find('.percent').html(Translator.trans('course_set.learn_progress') + '<br><span class="num">' + Math.round(percent) + '%</span>');
    }
  });
}

function triggerMemberExpired() {
  if ($('.member-expire').length) {
    $('.member-expire a').trigger('click');
  }
}

function remainTime() {
  var remainTime = parseInt($('#discount-endtime-countdown').data('remaintime'));
  if (remainTime >= 0) {
    var endtime = new Date(new Date().valueOf() + remainTime * 1000);
    $('#discount-endtime-countdown').countdown(endtime, function(event) {
      var $this = $(this).html(event.strftime(Translator.trans('course_set.show.count_down_format_hint')));
    }).on('finish.countdown', function() {
      $(this).html(Translator.trans('course_set.show.time_finish_hint'));
      setTimeout(function() {
        $.post(app.crontab, function() {
          window.location.reload();
        });
      }, 2000);
    });
  }
}

// 暂时去掉块状
// let orderLearnSwiper = null;
// $('.js-task-show-type').on('click', 'a', function() {
//     let $this = $(this).addClass('active');
//     $($this.data('list')).removeClass('hidden');
//     $($this.siblings('a').removeClass('active').data('list')).addClass('hidden');
//     if($this.data('type') == 'chart'&& !orderLearnSwiper) {
//       initSwiper();
//     }
// })
// 暂时去掉块状
// function initSwiper() {
//   orderLearnSwiper = new Swiper('.swiper-container',{
//     pagination: '.swiper-pager',
//     loop:true,
//     grabCursor: true,
//     paginationClickable: true
//   })
//   $('.arrow-left').on('click', function(e){
//     e.preventDefault()
//     orderLearnSwiper.swipePrev();
//   })
//   $('.arrow-right').on('click', function(e){
//     e.preventDefault()
//     orderLearnSwiper.swipeNext();
//   })
// }