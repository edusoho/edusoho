define(function(require, exports, module) {
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var WebUploader = require('../widget/web-uploader');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        
        var uploader = new WebUploader({
            element: '#upload-picture-btn',
        });

        uploader.on('uploadSuccess', function(file, response ) {
            var url = $("#upload-picture-btn").data("gotoUrl");
            Notify.success('上传成功！', 1);
            document.location.href = url;
        });

        $('#upload-picture-btn').click(function(){
            uploader.upload();
        })

        $('.use-partner-avatar').on('click', function(){
            var goto = $(this).data('goto');
            $.post($(this).data('url'), function(){
                window.location.href = goto;
            });
        });

    };

});