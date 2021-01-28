let $img = $('.img-js');
setInterval(wxpay_roll, 2000);

function wxpay_roll() {
  $.get($img.data('url'), function (response) {
    if (response) {
      window.location.href = $img.data('goto');
    }
  });
}
