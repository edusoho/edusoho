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