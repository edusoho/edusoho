$('#send-verify-email').click(function() {
  let $btn = $(this);
  $btn.button('loading');

  $.post($btn.data('url')).done(function() {
    window.location.reload();
  }).fail(function() {
    $btn.button('reset');
  });
});