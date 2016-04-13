define(function(require, exports, module) {
    var BatchUploader = require('./batch-uploader');
    var VideoQualitySwitcher = require('../widget/video-quality-switcher');

    exports.run = function() {

        var $el = $('#batch-uploader');
        var esuploader = new BatchUploader({
            element: $el,
            initUrl: $el.data('initUrl'),
            finishUrl: $el.data('finishUrl'),
            uploadAuthUrl: $el.data('uploadAuthUrl')
        });

        var switcher = null;
        if ($('.quality-switcher').length > 0) {
            switcher = new VideoQualitySwitcher({
                element: '.quality-switcher'
            });
        }

        esuploader.on('preupload', function(file){
            var quality = {videoQuality: switcher.get('videoQuality'), audioQuality: switcher.get('audioQuality')};
            esuploader.set('process', quality);
        });

        $el.parents('.modal').on('hidden.bs.modal', function(){
            window.location.reload();
        });


    };

});
