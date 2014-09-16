define(function(require, exports, module) {

    var ChunkUpload = require('edusoho.chunkupload');
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
		var chunkUpload = new ChunkUpload({
	        element: '#selectFiles',
            file_types : "*.*",
            file_size_limit : "10 MB"
	    });

	    chunkUpload.on("upload_start_handler", function(file) {
        	var self=this;
            var data = {};
	        $.ajax({
	            url: this.element.data('paramsUrl'),
	            async: false,
	            dataType: 'json',
	            data: data, 
	            cache: false,
	            success: function(response, status, jqXHR) {
	                self.setUploadURL(response.url);
	                self.setPostParams(response.postParams);
	            },
	            error: function(jqXHR, status, error) {
	                Notify.danger('请求上传授权码失败！');
	            }
	        });
        });

        chunkUpload.on("upload_progress_handler", function(file, bytesLoaded, bytesTotal) {
        	
        });

        chunkUpload.on("upload_success_handler", function(file, serverData) {
        	
        });

	    $("#btn_upload").on('click', function(){ chunkUpload.trigger("onSelectFileChange");});
	}
});