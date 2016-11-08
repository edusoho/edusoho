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

        var uploader = esuploader.uploader;

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

        $el.parents('.modal').on('hidden.bs.modal', function(){
            window.location.reload();
        });

        uploader.on('uploadError', function(file){
            if (!'retryNum' in file) {
                file.retryNum = 0;
            }

            if (file.retryNum < 3) {
                uploader.retry(file);
                file.retryNum = file.retryNum+1;
            }
        });

        $('.js-batch-create-lesson-btn').on('click', function() {
            var $btn = $(this);
            if ($btn.hasClass('disabled')) {
                return;
            }

            var fileStatus = esuploader.uploader.getStats();
            var cancelledFiles = esuploader.uploader.getFiles('cancelled');
            
            if (fileStatus.progressNum > 0 || cancelledFiles.length > 0) {
                Notify.danger(Translator.trans('还有文件未上传，请全部上传后再继续操作。'));
            } else if (fileStatus.successNum == 0) {
                Notify.danger(Translator.trans('请选择至少一个文件并上传。'));
            } else {
                $btn.button('loading');
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
                window.location.reload();
            }
        }
    };

});
