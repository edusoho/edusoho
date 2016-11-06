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

  if(file.length !== 0 && file.length !== undefined){
    let $minute = $('#minute');
    let $second = $('#second');
    let length = parseInt(file.length);
    let minute = parseInt(length / 60);
    let second = length % 60;
    $minute.val(minute);
    $second.val(second);
    file.minute = minute;
    file.second = second;
  }

  $('[name="media"]').val(JSON.stringify(file));
});