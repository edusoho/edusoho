import Base from './base';
import EsWebUploader from 'common/es-webuploader.js';

new Base();

let uploader = new EsWebUploader({
  element: '#upload-picture-btn',
  onUploadSuccess: function(file, response) {
    let url = $("#upload-picture-btn").data("gotoUrl");
    $.get(url, function(html) {
      $("#modal").modal({'show':true, 'backdrop':'static'}).html(html);
    })
  }
});
