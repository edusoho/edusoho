$('#modal').on('hidden.bs.modal', function () {
  $("#viewerIframe").attr('src', '');
});
