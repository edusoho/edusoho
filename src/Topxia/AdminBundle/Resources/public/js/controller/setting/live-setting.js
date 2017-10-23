define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var WebUploader = require('edusoho.webuploader');

    exports.run = function() {
        var $form = $("#live-setting-form");

        if($('#web-logo-upload').length>0) {
            var uploader = new WebUploader({
                element: '#web-logo-upload',
                accept: {  
                    title: 'Images',  
                    extensions: 'png',  
                    mimeTypes: 'image/png'  
                },
            });

            uploader.on('uploadSuccess', function(file, response ) {
                var url = $("#web-logo-upload").data("gotoUrl");
                
                $.get(url, function(html) {
                  $("#modal").modal('show').html(html);
                })
            });
        }

        if($('#app-logo-upload').length>0) {
            var uploader = new WebUploader({
                element: '#app-logo-upload',
                accept: {  
                    title: 'Images',  
                    extensions: 'png',  
                    mimeTypes: 'image/png'  
                },
            });

            uploader.on('uploadSuccess', function(file, response ) {
                var url = $("#app-logo-upload").data("gotoUrl");
                
                $.get(url, function(html) {
                  $("#modal").modal('show').html(html);
                })
            });
        }

        $(".logo-remove-btn-js").on('click', function(){
            if (!confirm(Translator.trans('确认要删除吗？'))) return false;
            var $btn = $(this);
            var type = $btn.data('type');
            $.post($btn.data('url'), function(){
                $btn.siblings('.logo-container-js').html('');
                $btn.hide();
                Notify.success(Translator.trans('删除直播课程LOGO成功！'));
            }).error(function(){
                Notify.danger(Translator.trans('删除直播课程LOGO失败！'));
            });
        });
    };

});