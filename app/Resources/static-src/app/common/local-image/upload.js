cd.upload({
  el: '.js-upload-input',
}).on('error', (code) => {
  if (code === 'FILE_SIZE_LIMIT') {
    cd.message({
      type: 'danger',
      message: Translator.trans('uploader.size_2m_limit_hint')
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
    localStorage.setItem('crop_image_attr', JSON.stringify(imageAttr));

    let loading = cd.loading({isFixed: true});
    
    $('#modal').html(loading).modal({
      backdrop: 'static',
      keyboard: false
    }).load($this.data('saveUrl'));
  });
});