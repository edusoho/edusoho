$modal = $('#modal');
$modal.on('hidden.bs.modal', function () {
  if ($("#lesson-preview-player").length > 0) {
    $("#lesson-preview-player").html("");
  }
});