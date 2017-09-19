function initUploadedFile() {
  let existFile = $('#selected-file').data();
  console.log(existFile);
  if (existFile) {
    $('[name="media"]').val(JSON.stringify(existFile));
  }
}

initUploadedFile();