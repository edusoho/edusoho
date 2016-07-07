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
            var params = {
                videoQuality: $('.video-quality-switcher').find('input[name=video_quality]:checked').val(), 
                audioQuality: $('.video-quality-switcher').find('input[name=video_audio_quality]:checked').val(),
                supportMobile: $('.video-quality-switcher').find('input[name=support_mobile]').val()
            };
            esuploader.set('process', params);
        });

        $el.parents('.modal').on('hidden.bs.modal', function(){
            window.location.reload();
        });

    };

});
