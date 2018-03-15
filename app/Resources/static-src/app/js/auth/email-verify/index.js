$.post($('[name=verifyUrl]').val(), function (response) {
  if (true == response) {
    setTimeout(function () {
      window.location.href = $('#jump-btn').attr('href');
    }, 2000);
  }
});