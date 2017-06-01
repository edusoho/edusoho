define(function(require, exports, module) {
    var BatchUploader = require('topxiawebbundle/controller/uploader/batch-uploader');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        var files = [];
        var $el = $('#batch-uploader');
        var $sortable = $('#sortable-list');
        var esuploader = new BatchUploader({
            element: $el,
            initUrl: $el.data('initUrl'),
            finishUrl: $el.data('finishUrl'),
            uploadAuthUrl: $el.data('uploadAuthUrl'),
            accept: $el.data('accept'),
            fileSingleSizeLimit: 1024 * 1024 * 1024 * 2,   //2G
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
                $btn.attr('disabled', 'disabled').text('创建任务中...');
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
                url: $('.js-batch-create-lesson-btn').data('url'),
                async: false,
                data: {fileId:file.resFile.id},
                success: function(response) {
                    if (response && response.error) {
                        Notify.danger(Translator.trans('创建任务失败,' + response.error, 3));
                    } else {
                        Notify.success(Translator.trans('创建任务成功'));
                        $sortable.append(response.html);
                    }
                },
                error: function(response) {
                    Notify.danger(Translator.trans('创建任务失败'));
                },
                complete: function (response) {
                    if (isLast) {
                        var data = $sortable.sortable("serialize").get();
                        $.post($sortable.data('sortUrl'), {ids : data}, function(){
                            window.location.reload();
                        });
                    }
                }
            });
        }
    };

});
