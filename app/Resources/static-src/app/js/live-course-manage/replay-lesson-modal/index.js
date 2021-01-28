$('a[role="replay-name-span"]').click(function(){
  let replayId = $(this).data('replayId');
  $(this).hide();
  $(`#replay-name-input-${replayId}`).show();
});

$('input[role="replay-name-input"]').blur(function() {
  let self = $(this);
  $(this).hide();
  let replayId = $(this).data('replayId');
  $(`#replay-name-span-${replayId}`).show();

  $.post(self.data('url'), {
    id: replayId, 
    title: self.val()
  }, function(res) {
    if (res) {
      $(`#replay-name-span-${replayId}`).text(self.val());
    }
  });
});