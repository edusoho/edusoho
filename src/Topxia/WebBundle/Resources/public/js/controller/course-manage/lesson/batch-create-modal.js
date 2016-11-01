define(function(require, exports, module) {
    var BatchUploader = require('topxiawebbundle/controller/uploader/batch-uploader');

    exports.run = function() {

        var fileIdArrs = [];
        var $el = $('#batch-uploader');
        var esuploader = new BatchUploader({
            element: $el,
            initUrl: $el.data('initUrl'),
            finishUrl: $el.data('finishUrl'),
            uploadAuthUrl: $el.data('uploadAuthUrl'),
            fileSingleSizeLimit: 1024 * 1024 * 1024 * 2   //2G
        });

        esuploader.on('preupload', function(file){
            var params = {
                videoQuality: $('.video-quality-switcher').find('input[name=video_quality]:checked').val(), 
                audioQuality: $('.video-quality-switcher').find('input[name=video_audio_quality]:checked').val(),
                supportMobile: $('.video-quality-switcher').find('input[name=support_mobile]').val()
            };
            esuploader.set('process', params);
        });

        esuploader.on('file.uploaded', function(file, data, response){
            fileIdArrs.push(response.id);
        });

        $el.parents('.modal').on('hidden.bs.modal', function()  {
            window.location.reload();
        });

        $('.js-batch-create-lesson-btn').on('click', function() {
            var $bth = $(this);

        });

        function createLessonByFileId(fileId)
        {
            $.ajax({
                type: 'post',
                async: false,
                url: $('.js-batch-create-lesson-btn').data('url'),
                data: {fileId:fileId},
                success: function(resp) {
                    
                },
                error: function(resp) {
                    
                }
            });
        }
    };

});
