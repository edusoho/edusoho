$.post($('#resend-email').data('url'));

$('#resend-email').on('click', function () {
  $('#email-sending').show();
  $('#email-send-success').hide();
  $.post($(this).data('url'), function (json) {

  }, 'json').complete(function () {
    $('#email-sending').hide();
    $('#email-send-success').show();
  });
});