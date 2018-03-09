import EsWebUploader from 'common/es-webuploader.js';
import notify from 'common/notify';

new EsWebUploader({
  element: '#upload-picture-btn',
  onUploadSuccess: function(file, response) {
    let url = $('#upload-picture-btn').data('gotoUrl');
    notify('success', Translator.trans('site.upload_success_hint'), 1);
    document.location.href = url;
  }
});

//论坛头像
$('.use-partner-avatar').on('click', function() {
  let $this = $(this);
  let goto = $this.data('goto');

  $.post($this.data('url'), {imgUrl: $this.data('imgUrl')}, function() {
    window.location.href = goto;
  });
});
