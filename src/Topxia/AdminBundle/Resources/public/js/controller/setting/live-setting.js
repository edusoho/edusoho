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
                uploader.get('uploader').removeFile(file, true);
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
                uploader.get('uploader').removeFile(file, true);
                $.get(url, function(html) {
                  $("#modal").modal('show').html(html);
                })
            });
        }

    };

});