import EsWebUploader from 'common/es-webuploader.js';
import notify from 'common/notify';

class Cover {
  constructor() {
    this.init();
  }

  init() {
    new EsWebUploader({
      element: '#upload-picture-btn',
      onUploadSuccess: function(file, response) {
        let url = $("#upload-picture-btn").data("gotoUrl");
        notify('success', Translator.trans('上传成功！'), 1);

        document.location.href = url;
      }
    });
  }
}

new Cover();


//  define(function(require, exports, module) {
//     var Notify = require('common/bootstrap-notify');
//     var WebUploader = require('edusoho.webuploader');

//     exports.run = function() {
//         var uploader = new WebUploader({
//             element: '#upload-picture-btn'
//         });

//         uploader.on('uploadSuccess', function(file, response ) {
//             var url = $("#upload-picture-btn").data("gotoUrl");
//             Notify.success(Translator.trans('上传成功！'), 1);
//             document.location.href = url;
//         });

//     };

// });
