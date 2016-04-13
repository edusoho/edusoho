define(function(require, exports, module) {
    var BatchUploader = require('./batch-uploader');

    exports.run = function() {

        var $el = $('#batch-uploader');
        var esuploader = new BatchUploader({
            element: $el,
            initUrl: $el.data('initUrl'),
            finishUrl: $el.data('finishUrl'),
            uploadAuthUrl: $el.data('uploadAuthUrl')
        });

        esuploader.on('preupload', function(file){
            var quality = {
                videoQuality: $('.video-quality-switcher').find('input[name=video_quality]:checked'), 
                audioQuality: $('.video-quality-switcher').find('input[name=audio_quality]:checked')
            };
            esuploader.set('process', quality);
        });

        $el.parents('.modal').on('hidden.bs.modal', function(){
            window.location.reload();
        });

    };

});
