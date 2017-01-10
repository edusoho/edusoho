$('#modal').on('hidden.bs.modal', function () {

  console.log($(this).find('.modal-body'));
  if ($("#lesson-preview-player").length > 0) {
    $("#lesson-preview-player").remove();
  }

  if ($("#lesson-preview-iframe").length > 0) {
    $("#lesson-preview-iframe").remove();
  }
});
