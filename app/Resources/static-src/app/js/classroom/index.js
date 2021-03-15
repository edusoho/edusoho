import Sign from 'app/common/widget/sign';
import 'app/common/widget/qrcode';
import 'app/common/widget/cancel-refund';
import { buyBtn } from 'app/common/widget/btn-util';

buyBtn($('.js-classroom-buy-btn'));

if ($('#classroom-sign').length > 0) {
  let userSign = new Sign($('#classroom-sign'));
}

if ($('.icon-vip').length > 0) {
  $('.icon-vip').popover({
    trigger: 'manual',
    placement: 'auto top',
    html: 'true',
    container: 'body',
    animation: false
  }).on('mouseenter', function () {
    let _this = $(this);
    _this.popover('show');

  }).on('mouseleave', function () {
    let _this = $(this);
    setTimeout(function () {
      if (!$('.popover:hover').length) {
        _this.popover('hide');
      }
    }, 100);
  });
}
let colorPrimary = $('.color-primary').css('color');
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