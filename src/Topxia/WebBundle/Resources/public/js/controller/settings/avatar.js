define(function(require, exports, module) {
    var WebUploader = require('edusoho.webuploader');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        
        var uploader = new WebUploader({
            element: '#upload-picture-btn'
        });

        uploader.on('uploadSuccess', function(file, response ) {
            var url = $("#upload-picture-btn").data("gotoUrl");
            Notify.success('上传成功！', 1);
            document.location.href = url;
        });

        $('.use-partner-avatar').on('click', function(){
            var $this = $(this);
            var goto = $this.data('goto');

            $.post($this.data('url'), {imgUrl:$this.data('imgUrl')},function(){
                window.location.href = goto;
            });
        });
    };

});