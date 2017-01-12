import { chapterAnimate } from 'app/common/widget/chapter-animate';

chapterAnimate();
initTaskLeranChart();

$('.js-task-show-type').on('click','a',function() {
  let $this = $(this).addClass('active');
  $($this.data('list')).removeClass('hidden');
  $($this.siblings('a').removeClass('active').data('list')).addClass('hidden');
})

function initTaskLeranChart() {
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



