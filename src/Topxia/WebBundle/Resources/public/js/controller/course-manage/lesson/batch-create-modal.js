define(function(require, exports, module) {
    var BatchUploader = require('topxiawebbundle/controller/uploader/batch-uploader');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var files = [];
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
            var file = {cFile:file, resFile:response};
            files.push(file);
        });

        $('.js-batch-create-lesson-btn').on('click', function() {
            var fileStatus = esuploader.uploader.getStats();
            if (fileStatus.progressNum > 0) {
                Notify.danger(Translator.trans('还有文件再上传,请等待所有文件上传完成'));
            } else if (fileStatus.successNum == 0) {
                Notify.danger(Translator.trans('还没有上传成功的文件'));
            } else {
                var $bth = $(this);
                $.each(files, function(index , file){
                    var isLast = index+1 == files.length;
                    createLessonByFile(file, isLast);
                });
            }

        });

        function createLessonByFile(file, isLast)
        {
            var $statusCol = $('#'+file.cFile.id).find('.file-status');
            $.ajax({
                type: 'post',
                async: false,
                url: $('.js-batch-create-lesson-btn').data('url'),
                data: {fileId:file.resFile.id},
                success: function(resp) {
                    $statusCol.addClass('text-success').html(Translator.trans('创建课时成功'));
                },
                error: function(resp) {
                    $statusCol.addClass('text-danger').html(Translator.trans('创建课时失败'));
                }
            });

            if (isLast) {
                if (confirm(Translator.trans('批量创建课时成功，是否要刷新页面?'))) {
                    window.location.reload();
                }
            }
        }
    };

});
