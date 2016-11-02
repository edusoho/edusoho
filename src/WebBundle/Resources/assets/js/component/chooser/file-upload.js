let $uploader = $('#uploader-container');

let uploaderSdk = new UploaderSDK({
  id: $uploader.attr('id'),
  initUrl: $uploader.data('initUrl'),
  finishUrl: $uploader.data('finishUrl'),
  accept: $uploader.data('accept'),
  process: $uploader.data('process')
});



uploaderSdk.on('file.finish', (file) => {
  file.source = 'self';
  $('[name="media"]').val(JSON.stringify(file));
});


