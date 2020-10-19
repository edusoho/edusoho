import Sign from 'app/common/widget/sign';
import 'app/common/widget/qrcode';
import 'app/common/widget/cancel-refund';
import { buyBtn } from 'app/common/widget/btn-util';

$('.js-classroom-buy-before-btn').on('click', function () {
  $.ajax({
    type: "GET",
    data: {
      'targetType': $(this).data('targetType'),
      'targetId': $(this).data('targetId'),
    },
    beforeSend: function (request) {
      request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
      request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
    },
    url: '/api/information_collect_event/buy_before',
  }).done(function (resp) {
    if (resp && resp.status =='open'){
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