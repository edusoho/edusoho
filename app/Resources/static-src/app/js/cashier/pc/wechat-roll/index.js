$('#modal').on('hidden.bs.modal', function () {
  clearInterval(window.intervalWechatId);
});

window.intervalWechatId = setInterval(() => {
  $.get($('.js-qrcode-img').data('url'), resp => {
    if (resp.isPaid) {
      location.href = resp.redirectUrl;
    }
  });
}, 2000);