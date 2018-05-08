$('body').on('click', '.es-qrcode', function () {
  var $this = $(this);
  if ($this.hasClass('open')) {
    $this.removeClass('open');
  } else {
    $.ajax({
      type: 'post',
      url: $this.data('url'),
      dataType: 'json',
      success: function (data) {
        $this.find('.qrcode-popover img').attr('src', data.img);
        $this.addClass('open');
      }
    });
  }
});