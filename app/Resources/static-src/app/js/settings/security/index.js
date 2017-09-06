import notify from 'common/notify';

$('#send-verify-email').click(function() {
  let $btn = $(this);
  $btn.button('loading');

  $.post($btn.data('url')).done(function(data) {
    notify('success', Translator.trans(data.message));
    window.location.reload();
    
  }).fail(function(data) {
    $btn.button('reset');
    notify('danger',  Translator.trans(data.responseJSON.message));
  });
});