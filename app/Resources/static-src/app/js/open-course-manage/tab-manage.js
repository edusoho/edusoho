$('a[data-role="pick-modal"]').click(function () {
  $('#modal').html('');
  $('#modal').load($(this).data('url'));
});