import Sign from 'app/common/widget/sign';
import 'app/common/widget/qrcode';

let buy_btn = false;

$('.buy-btn').click(function () {
  if (!buy_btn) {
    $('.buy-btn').addClass('disabled');
    buy_btn = true;
  }
  return true;
});

$(".cancel-refund").on('click', function () {
  if (!confirm(Translator.trans('真的要取消退款吗？'))) {
    return false;
  }

  $.post($(this).data('url'), function () {
    window.location.reload();
  });
});

$("#quit").on('click', function () {
  if (!confirm(Translator.trans('确定退出班级吗？'))) {
    return false;
  }

  $.post($(this).data('url'), function () {
    window.location.reload();
  });
});


if ($('#classroom-sign').length > 0) {
  let userSign = new Sign($('#classroom-sign'));
}

if ($('.icon-vip').length > 0) {
  $(".icon-vip").popover({
    trigger: 'manual',
    placement: 'auto top',
    html: 'true',
    container: 'body',
    animation: false
  }).on("mouseenter", function () {
    let _this = $(this);
    _this.popover("show");

  }).on("mouseleave", function () {
    let _this = $(this);
    setTimeout(function () {
      if (!$(".popover:hover").length) {
        _this.popover("hide")
      }
    }, 100);
  });
}
