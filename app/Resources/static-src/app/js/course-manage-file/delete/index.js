let $form = $('#material-delete-form');

$('.material-delete-form-btn').click(function() {
  $(this).button('loading').addClass('disabled');

  let ids = [];
  $('[data-role=batch-item]:checked').each(function() {
    ids.push(this.value);
  });

  let isDeleteFile = $form.find('input[name="isDeleteFile"]:checked').val();
  $.post($form.attr('action'), {
    ids: ids,
    isDeleteFile: isDeleteFile
  }, function() {
    window.location.reload();
  });
});