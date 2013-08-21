define(function(require, exports, module) {

    require('swfupload');

    exports.run = function() {

        function UploadProgressBar(element) {
            this.element = $(element);
        }

        UploadProgressBar.prototype.setProgress = function (percentage) {
            this.element.find('.progress-bar').css('width', percentage + '%');
        }

        UploadProgressBar.prototype.setComplete = function () {
            this.setProgress(100);
        }

        var progressbar = new UploadProgressBar('#upload-progress');

        var token = $("#fileuploadbtn").data('token');

        var swfu = new SWFUpload({
            upload_url : "http://up.qiniu.com/",
            post_params : {
                "key" : "test/test_a.mp4",
                "token" : token
            },
            file_types : "*.*",
            file_types_description : "视频",
            file_size_limit : "100 MB",
            file_upload_limit : 1,
            file_queue_limit: 1,
            file_post_name: 'file',

            button_placeholder_id : "fileuploadbtn",
            button_width: "65",
            button_height: "29",
            button_text: "上传",

            file_dialog_complete_handler: function(numFilesSelected, numFilesQueued) {
                if (numFilesSelected == 0) {
                    return;
                }
                if (numFilesSelected > 1) {
                    alert('一次只能上传一个文件，请重新选择。');
                    return ;
                }

                if (numFilesQueued == 0) {
                    alert('文件正在上传中，不能再次上传。');
                    return ;
                }

                console.log('file dialog complete:', numFilesSelected, numFilesQueued);
                this.startUpload();
            },

            upload_start_handler: function(file) {
                console.log('upload start', file);
                progressbar.setProgress(0);
            },

            upload_progress_handler: function(file, bytesLoaded, bytesTotal) {
                var percentage = Math.ceil((bytesLoaded / bytesTotal) * 100);
                console.log('upload progress:', percentage);
                progressbar.setProgress(percentage);
            },

            upload_error_handler: function(file, errorCode, message) {
                console.log('upload error:', file, errorCode, message);
            },

            upload_success_handler: function(file, serverData) {
                serverData = $.parseJSON(serverData);
                console.log('upload success', file, serverData);
                progressbar.setComplete();
            }
        });

    };

});