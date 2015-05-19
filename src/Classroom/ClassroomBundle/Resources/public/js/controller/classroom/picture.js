 define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var WebUploader = require('edusoho.webuploader');

    exports.run = function() {
        var uploader = new WebUploader({
            element: '#upload-picture-btn'
        });

        uploader.on('uploadSuccess', function(file, response ) {
            var url = $("#upload-picture-btn").data("gotoUrl");
            Notify.success('上传成功！', 1);
            document.location.href = url;
        });

    };

});

