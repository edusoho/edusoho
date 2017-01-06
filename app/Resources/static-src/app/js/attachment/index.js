const $uploader = $('#uploader-container');

let uploader = new UploaderSDK({
  id: $uploader.attr('id'),
  initUrl: $uploader.data('initUrl'),
  finishUrl: $uploader.data('finishUrl'),
  accept: $uploader.data('accept'),
  process: $uploader.data('process'),
  fileSingleSizeLimit: $uploader.data('fileSingleSizeLimit'),
  ui: 'single'
});

uploader.on('file.finish', (file) => {
  if (file.length && file.length > 0) {
    let minute = parseInt(file.length / 60);
    let second = Math.round(file.length % 60);
    $("#minute").val(minute);
    $("#second").val(second);
    $("#length").val(minute * 60 + second);
  }

  const $metas = $('[data-role="metas"]');
  const $ids = $('.' + $metas.data('idsClass'));
  const $list = $('.' + $metas.data('listClass'));

  $.get('/attachment/file/' + file.id + '/show', function (html) {
    $list.append(html);
    $ids.val(file.id);
    $('#attachment-modal').modal('hide');
    $list.siblings('.js-upload-file').hide();
  })
});

//只执行一次
$('#attachment-modal').one('hide.bs.modal', (event) => {
  uploader.destroy();
  uploader = null;
});