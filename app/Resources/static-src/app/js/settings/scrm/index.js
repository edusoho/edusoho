let $target = $('#scrm-qrcode');
if (typeof($target.data('url')) != 'undefined') {
  $.get($target.data('url'), res => {
    if (res !=='') {
      $target.attr('src', res.img);
    }
  });
}