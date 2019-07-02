let $form = $('#material-delete-form');

$('.material-delete-form-btn').click(function() {
  $(this).button('loading').addClass('disabled');

  let ids = $("input[name='deleteIds']").val();

  let isDeleteFile = $form.find('input[name="isDeleteFile"]:checked').val();
  $.post($form.attr('action'), {
    ids: ids,
    isDeleteFile: isDeleteFile
  }, function() {
    window.location.reload();
  });
});