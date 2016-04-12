define(function(require, exports, module) {

    var WebUploader = require('edusoho.webuploader');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        
        var uploader = new WebUploader({
            element: '#upload-picture-btn'
        });

        uploader.on('uploadSuccess', function(file, response ) {
            var url = $("#upload-picture-btn").data("gotoUrl");

            $('#modal').load(url);
            Notify.success(Translator.trans('上传成功！'), 1);
            
        });


        $('.use-partner-avatar').on('click', function(){
            var goto = $(this).data('goto');
            $.post($(this).data('url'), function(){
                window.location.href = goto;
            });
        });

    };

});