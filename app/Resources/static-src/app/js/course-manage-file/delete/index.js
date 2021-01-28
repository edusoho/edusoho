let $form = $('#material-delete-form');

$('.material-delete-form-btn').click(function() {
  $(this).button('loading').addClass('disabled');

  $.post($form.attr('action'), $form.serialize(), function () {
    window.location.reload();
  });
});