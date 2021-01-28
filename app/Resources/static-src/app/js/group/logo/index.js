import EsWebUploader from 'common/es-webuploader.js';
import notify from 'common/notify';

new EsWebUploader({
  element: '#group-save-btn',
  onUploadSuccess: function(file, response) {
    let url = $('#group-save-btn').data('gotoUrl');
    notify('success', Translator.trans('site.upload_success_hint'));
    document.location.href = url;
  }
});
