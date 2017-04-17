const $unfavorite = $('.js-unfavorite-btn');
const $favorite = $('.js-favorite-btn');

const bindOperation = ($needHideBtn, $needShowBtn) => {
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
};

bindOperation($unfavorite, $favorite);
bindOperation($favorite, $unfavorite);


$("body").on("click",".es-qrcode", (event) => {
  const $this = $(event.currentTarget);
  if($this.hasClass('open')) {
    $this.removeClass('open');
  }else {
    $.ajax({
      type: "post",
      url: $this.data("url"),
      dataType: "json",
      success:function(data){
        $this.find(".qrcode-popover img").attr("src",data.img);
        $this.addClass('open');
      }
    });

  }
})

$(".cancel-refund").on('click', function(){
    if (!confirm('course_set.refund_cancel_hint')) {
        return false;
    }

    $.post($(this).data('url'), function(data){
        window.location.reload();
    });
});