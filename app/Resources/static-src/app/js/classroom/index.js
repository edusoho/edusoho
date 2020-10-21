import Sign from 'app/common/widget/sign';
import 'app/common/widget/qrcode';
import 'app/common/widget/cancel-refund';
import { buyBtn } from 'app/common/widget/btn-util';
import Api from 'common/api';

$('.js-classroom-buy-before-btn').on('click', function () {
  Api.informationCollect.getEvent({
    params: {
      action: 'buy_before',
    },
    data: {
      targetType: $(this).data('targetType'),
      targetId: $(this).data('targetId'),
    }
  }).then(resp => {
    if (resp && resp.status === 'open'){
      $.get('/information_collect/event/' + resp.id, resp => {
        if (typeof resp === 'object') {
          window.location.href = resp.url;
        } else {
          $('#modal').modal('show').html(resp);
        }
      });
    }else{
      $.post($('.js-classroom-buy-before-btn').data('url'), resp => {
        if (typeof resp === 'object') {
          window.location.href = resp.url;
        } else {
          $('#modal').modal('show').html(resp);
        }
      });
    }
  });
});

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