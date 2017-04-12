import 'app/common/widget/qrcode';

let $unfavorite = $('.js-unfavorite-btn');
let $favorite = $('.js-favorite-btn');
bindOperation($unfavorite, $favorite);
bindOperation($favorite, $unfavorite);

$(".cancel-refund").on('click', function () {
  if (!confirm('真的要取消退款吗？')) {
    return false;
  }
  $.post($(this).data('url'), function (data) {
    window.location.reload();
  });
});


function bindOperation($needHideBtn, $needShowBtn) {
  $needHideBtn.click(() => {
    const url = $needHideBtn.data('url');
    console.log(url);
    if (!url) {
      return;
    }
    $.post(url)
      .done((success) => {
        if (!success) return;
        $needShowBtn.show();
        $needHideBtn.hide();
      });
  });
}
