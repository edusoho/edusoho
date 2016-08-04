define(function(require, exports, module) {
    "use strict";
    var BatchUploader = require('../uploader/batch-uploader');
    
    exports.run = function() {

        var $el = $('#batch-uploader');
        var esuploader = new BatchUploader({
            element: $el,
            initUrl: $el.data('initUrl'),
            finishUrl: $el.data('finishUrl'),
            uploadAuthUrl: $el.data('uploadAuthUrl'),
            fileSingleSizeLimit: $el.data('fileSingleSizeLimit'),
            multi: false,
        });


        esuploader.on('preupload', function(file) {
            var params = {
                videoQuality: $('.video-quality-switcher').find('input[name=video_quality]:checked').val(),
                audioQuality: $('.video-quality-switcher').find('input[name=video_audio_quality]:checked').val(),
                supportMobile: $('.video-quality-switcher').find('input[name=support_mobile]').val()
            };
            esuploader.set('process', params);
        });

        var $list = $("." + esuploader.element.data('listClass'));
        var idStore = $("." + esuploader.element.data('idsClass'));
        idStore.addId = function(id) {
            var id_str = this.val();
            var ids;
            if (id_str === '') {
                ids = [];
            } else {
                ids = id_str.split(',');
            }

            ids.push(id);
            this.val(ids.join(','));
        };

        idStore.removeId = function(id) {
            var id_str = this.val();
            if (id_str === '') {
                return;
            }

            var ids = id_str.split(',');
            var index = ids.indexOf(id);
            if (index <= -1) {
                return;
            }

            ids.splice(index, 1);
            this.val(ids.join(','));
        };

        esuploader.on('file.uploaded', function(file, data, response) {
            $list.empty();
            idStore.val('');

            $.get('/attachment/file/'+ response.id +'/show', function(html){
                $list.append(html);
            })
            idStore.addId(response.id);
            fileUploaded(file);
        });

        function fileUploaded(file) {
            //移除文件
            esuploader.uploader.removeFile(file, true);
            $(".balloon-filelist ul >li").remove();
            //隐藏modal框
            $("#modal").modal("hide");

            $list.next('a').addClass('hidden');
        }
    }
});