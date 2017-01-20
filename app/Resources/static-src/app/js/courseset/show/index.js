import { chapterAnimate } from 'app/common/widget/chapter-animate';
import { Card } from 'app/common/unit/card';
import Swiper from 'Swiper';
let orderLearnSwiper = null;

Card();
chapterAnimate();
initTaskLearnChart();
triggerMemberExpired();

$('.js-task-show-type').on('click', 'a', function() {
    let $this = $(this).addClass('active');
    $($this.data('list')).removeClass('hidden');
    $($this.siblings('a').removeClass('active').data('list')).addClass('hidden');
    if($this.data('type') == 'chart'&& !orderLearnSwiper) {
      initSwiper();
    }
})

function initTaskLearnChart() {
    $('#freeprogress').easyPieChart({
        easing: 'easeOutBounce',
        trackColor: '#ebebeb',
        barColor: '#46c37b',
        scaleColor: false,
        lineWidth: 14,
        size: 145,
        onStep: function(from, to, percent) {
            if (Math.round(percent) == 100) {
                $(this.el).addClass('done');
            }
            $(this.el).find('.percent').html('学习进度' + '<br><span class="num">' + Math.round(percent) + '%</span>');
        }
    });

    $('#orderprogress-plan').easyPieChart({
        easing: 'easeOutBounce',
        trackColor: '#ebebeb',
        barColor: '#fd890c',
        scaleColor: false,
        lineWidth: 14,
        size: 145,
    });

    let bg = $('#orderprogress-plan').length > 0 ? 'transparent' : '#ebebeb';

    $('#orderprogress').easyPieChart({
        easing: 'easeOutBounce',
        trackColor: bg,
        barColor: '#46c37b',
        scaleColor: false,
        lineWidth: 14,
        size: 145,
        onStep: function(from, to, percent) {
            if (Math.round(percent) == 100) {
                $(this.el).addClass('done');
            }
            $(this.el).find('.percent').html('学习进度' + '<br><span class="num">' + Math.round(percent) + '%</span>');
        }
    });
}

function triggerMemberExpired() {
    if ($('.member-expire').length) {
        $(".member-expire a").trigger('click');
    }
}

function initSwiper() {
  orderLearnSwiper = new Swiper('.swiper-container',{
    pagination: '.swiper-pager',
    loop:true,
    grabCursor: true,
    paginationClickable: true
  })
  $('.arrow-left').on('click', function(e){
    e.preventDefault()
    orderLearnSwiper.swipePrev()
  })
  $('.arrow-right').on('click', function(e){
    e.preventDefault()
    orderLearnSwiper.swipeNext()
  })

  // $('data-toggle="tooltip"').tooTip()
}

