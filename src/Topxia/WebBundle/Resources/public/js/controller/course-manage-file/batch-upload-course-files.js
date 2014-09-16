define(function(require, exports, module) {

    var ChunkUpload = require('edusoho.chunkupload');
    var Notify = require('common/bootstrap-notify');
    var UploadProgressBar = require('edusoho.uploadProgressBar');
    var VideoQualitySwitcher = require('../widget/video-quality-switcher');

    function getFileSize(size) {
    	return (size / (1024 * 1024)).toFixed(2) + "MB";
	}

	function stopUpload(chunkUpload){
		var uploadButton = $("#btn_upload");
    	uploadButton.find("span").text("继续");
    	chunkUpload.stopUpload();
    	uploadButton.unbind("click");
    	uploadButton.on("click", function(){
    		continueUpload(chunkUpload);
    	});
	}

	function continueUpload(chunkUpload){
		var uploadButton = $("#btn_upload");
    	uploadButton.find("span").text("暂停");
    	chunkUpload.continueUpload();
    	uploadButton.unbind("click");
    	uploadButton.on("click", function(){
    		stopUpload(chunkUpload);
    	});
	}

	function uploadStart(file, self, switcher) {
        var data = {};
        var targetType = self.element.data('targetType');
		var uploadMode = self.element.data('uploadMode');
		var hlsEncrypted = self.element.data('hlsEncrypted');
		if (targetType == 'courselesson' && uploadMode == 'cloud') {
			if (file.type == 'audio/mpeg') {
				data.convertor = '';
			} else if ( (file.type == 'application/vnd.ms-powerpoint') || (file.type == 'application/vnd.openxmlformats-officedocument.presentationml.presentation') ) {
				data.convertor = 'ppt';
			} else {
				if (switcher) {
					data.videoQuality = switcher.get('videoQuality');
					data.audioQuality = switcher.get('audioQuality');
					if (hlsEncrypted) {
						data.convertor = 'HLSEncryptedVideo';
					} else {
						data.convertor = 'HLSVideo';
					}
				}
			}
		}
        $.ajax({
            url: self.element.data('paramsUrl'),
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
    }

    function fileSelected(files){
        var fileQueue = this.get('fileQueue');
        var startLength=0;
        if(fileQueue) {
            startLength = fileQueue.length;
        }
    	for (var i = 0; i < files.length; i++) {
    		var file = files[i];
            var index = (i+startLength);
        	var tr = "<tr>";
			tr += "<td>"+file.name+"</td>";
			tr += "<td>"+getFileSize(file.size)+"</td>";
			tr += "<td id='file_"+index+"'>";
			tr += "<div class='progress' id='fileProgressBar"+index+"'>";
			tr += "<div class='progress-bar' role='progressbar' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100' style='width: 0%; text-align:left;'>未开始</div>";
          	tr += "</div>";
          	tr += "</td>";
			tr += "</tr>";
			$("#fileList table tbody").prepend($(tr));
			var progressbar = new UploadProgressBar({
                element: "#fileProgressBar"+index
            });
            $("#fileProgressBar"+index).data("progressbar",progressbar);
    	};
    }

    exports.run = function() {
		var chunkUpload = new ChunkUpload({
	        element: '#selectFiles',
            file_types : "*.mp3;*.mp4;*.avi;*.flv;*.wmv;*.mov;*.ppt;*.pptx",
            file_size_limit : "1 GB",
            uploadOnSelected: false
	    });

		var switcher = null;
		if ($('.quality-switcher').length > 0) {
		    switcher = new VideoQualitySwitcher({
				element: '.quality-switcher'
			});
		}

	    chunkUpload.on("upload_start_handler", function(file){
	    	uploadStart(file, this, switcher);
	    });

        chunkUpload.on("fileSelected", fileSelected);

        chunkUpload.on("upload_progress_handler", function(file, bytesLoaded, bytesTotal, fileIndex) {
        	var percentage = Math.ceil((bytesLoaded / bytesTotal) * 100);
        	$("div[role='progressbar']","#fileProgressBar"+fileIndex).text("上传中");
            $("#fileProgressBar"+fileIndex).data("progressbar").setProgress(percentage);
        });

        chunkUpload.on("upload_success_handler", function(file, serverData, fileIndex) {
        	if (this.element.data('callback')) {
        		serverData = $.parseJSON(serverData);
                $.post(this.element.data('callback'), serverData, function(response) {
        			$("div[role='progressbar']", "#fileProgressBar"+fileIndex).text("完成");
                }, 'json');
            }
        });

	    $("#btn_upload").on('click', function(){
	    	var uploadButton = $("#btn_upload");
	    	$("#selectFiles").prop("disabled",true);
	    	chunkUpload.trigger("upload", chunkUpload.get("fileQueue").length-1);
	    	uploadButton.unbind("click");
	    	uploadButton.find("span").text("暂停");
	    	uploadButton.on("click", function(){
	    		stopUpload(chunkUpload);
	    	});
	    });

	    $(".modal").on("hide.bs.modal", function(){
            chunkUpload.destroy();
        });
	}
});