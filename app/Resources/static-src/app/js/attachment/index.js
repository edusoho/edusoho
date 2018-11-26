import notify from 'common/notify';

let $modal = $('#attachment-modal');
let $uploader = $modal.find('#uploader-container');

const uploadProcess = {
  document: {
    type: 'html',
  },
};

let uploader = new UploaderSDK({
  id: $uploader.attr('id'),
  sdkBaseUri: app.cloudSdkBaseUri,
  disableDataUpload: app.cloudDisableLogReport,
  disableSentry: app.cloudDisableLogReport,
  initUrl: $uploader.data('initUrl'),
  finishUrl: $uploader.data('finishUrl'),
  accept: $uploader.data('accept'),
  process: uploadProcess,
  fileSingleSizeLimit: $uploader.data('fileSingleSizeLimit'),
  ui: 'single',
  locale: document.documentElement.lang
});

uploader.on('error', function(type) {
  notify('danger', type.message);
});

uploader.on('file.finish', (file) => {
  if (file.length && file.length > 0) {
    let minute = parseInt(file.length / 60);
    let second = Math.round(file.length % 60);
    $('#minute').val(minute);
    $('#second').val(second);
    $('#length').val(minute * 60 + second);
  }

  const $metas = $('[data-role="metas"]');
  const currentTarget = $metas.data('currentTarget');

  let $ids = $('.' + $metas.data('idsClass'));
  let $list = $('.' + $metas.data('listClass'));
  if (currentTarget != '') {
    $ids = $('[data-role='+currentTarget+']').find('.' + $metas.data('idsClass'));
    $list = $('[data-role='+currentTarget+']').find('.' + $metas.data('listClass'));
  }

  $.get('/attachment/file/' + file.id + '/show', function (html) {
    $list.append(html);
    $ids.val(file.id);
    $modal.modal('hide');
    $list.siblings('.js-upload-file').hide();
  });
});

//只执行一次
$modal.one('hide.bs.modal', (event) => {
  uploader.destroy();
  uploader = null;
});
