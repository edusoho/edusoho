 define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    var WebUploader = require('edusoho.webuploader');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var uploader = new WebUploader({
            element: '#group-save-btn'
        });

        uploader.on('uploadSuccess', function(file, response ) {
            var url = $("#group-save-btn").data("gotoUrl");
            Notify.success('上传成功！', 1);
            document.location.href = url;
        });


    };

});

