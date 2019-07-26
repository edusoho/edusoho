import EsWebUploader from 'common/es-webuploader.js';

class Cover {
  constructor() {
    this.init();
  }

  init() {
    new EsWebUploader({
      element: '#upload-picture-btn',
      onUploadSuccess: function(file, response) {
        $('#upload-picture-btn').button('loading');
        let url = $('#upload-picture-btn').data('gotoUrl');
        cd.message({type: 'success', message: Translator.trans('site.upload_success_hint')});
        document.location.href = url;
      }
    });
  }
}

new Cover();
