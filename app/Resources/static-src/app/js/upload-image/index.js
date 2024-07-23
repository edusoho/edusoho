const el = '.js-upload-input';
const $el = $(el);
cd.upload({
  el: el,
  fileSize: 5
}).on('error', (code) => {
  $el.val('');
  if (code === 'FILE_SIZE_LIMIT') {
    cd.message({
      type: 'danger',
      message: Translator.trans('uploader.size_5m_limit_hint')
    });
  } else if (code === 'FLIE_TYPE_LIMIT') {
    cd.message({
      type: 'danger',
      message: Translator.trans('uploader.type_denied_limit_hint')
    });
  }
}).on('success', (event, file, src) => {
  cd.crop({
    event,
    src,
  }).on('success', (imageAttr) => {
    let $this = $(event.currentTarget);
    const imageAttrJson = JSON.stringify(imageAttr);
    if (imageAttrJson.length > 5 * 1024 * 1024) {
      $('[name=crop_image_attr]').val(imageAttrJson);
      localStorage.setItem('crop_image_attr', 'get_from_dom');
    } else {
      localStorage.setItem('crop_image_attr', imageAttrJson);
    }
    let loading = cd.loading({isFixed: true});
    const $modal = $('#modal');
    $modal.html(loading).modal({
      backdrop: 'static',
      keyboard: false
    }).load($this.data('saveUrl'));

    $modal.on('hidden.bs.modal', () => {
      $el.val('');
    })
  });
});