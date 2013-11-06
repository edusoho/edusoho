define(function(require, exports, module) {

    require('jquery.plupload-queue-css');
    require('jquery.plupload-queue');

    exports.run = function() {

        var params = $('#pickfile').data();

    $("#uploader").pluploadQueue({
        // General settings
        runtimes : 'html5, flash',
        url : params.uploadUrl
    });

    return;


        console.log(params);

        var uploader = new plupload.Uploader({
            runtimes : 'html5,flash',
            browse_button : 'pickfile',
            max_file_size : '100mb',
            url : params.uploadUrl,
            filters : [
                {title : "Video files", extensions : "mp4,avi"}
            ],
            multipart_params: {
                "key" : 'test.mp4',
                "token" : params.uploadToken,
            }
        });

        uploader.bind('FilesAdded', function(up, files) {
            console.log('FilesAdded', uploader);
            // uploader.refresh();
            // uploader.start();
        });

        uploader.bind('FileUploaded', function(uploader, file, response){
            console.log('FileUploaded', file, response);
        });

        uploader.bind('BeforeUpload', function(uploader, file){
            console.log('BeforeUpload', file, file);
        });

        uploader.bind('UploadProgress', function(uploader, file){
            console.log('UploadProgress', file);
        });

        uploader.bind('UploadFile', function(uploader, file){
            console.log('UploadFile', file);
        });

        uploader.bind('Error', function(uploader, error){
            console.log('Error', error);
        });

        uploader.bind('QueueChanged', function(uploader){
            console.log('QueueChanged', uploader);
            // uploader.start();
        });

    $('#uploadfile').click(function(e) {
        uploader.start();
        e.preventDefault();
    });


        uploader.init();

        console.log('init:', uploader.state);


    };

});