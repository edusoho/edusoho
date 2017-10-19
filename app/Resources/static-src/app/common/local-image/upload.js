cd.upload({
  el: '.js-upload-input',
  type: 'crop',
  error(code) {
    if (code === 'FILE_SIZE_LIMIT') {
      notify('danger', Translator.trans('uploader.size_2m_limit_hint'));
    } else if (code === 'FLIE_TYPE_LIMIT') {
      notify('danger', Translator.trans('uploader.type_denied_limit_hint'));
    }
  },
  success(event, $image) {
    let $this = $(event.currentTarget);
    $('body').append($image.addClass('js-source-img'));

    let loading = `
      <div class="cd-loading cd-loading-fixed">
        <div class="loading-content">
          <div></div>
          <div></div>
          <div></div>
        </div>
      </div>
    `;

    $('#modal').html(loading).modal({
      backdrop: 'static',
      keyboard: false
    }).load($this.data('saveUrl'));
  }
});