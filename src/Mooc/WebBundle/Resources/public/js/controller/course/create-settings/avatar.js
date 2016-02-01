define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    require('jquery.form');
    var WebUploader = require('edusoho.webuploader');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var uploader = new WebUploader({
            element: '#upload-picture-btn',
        });

        uploader.on('uploadSuccess', function(file, response ) {
            var url = $("#upload-picture-btn").data("gotoUrl");
            Notify.success('上传成功！', 1);

            $('#modal').load(url);
        });

       
    };

});