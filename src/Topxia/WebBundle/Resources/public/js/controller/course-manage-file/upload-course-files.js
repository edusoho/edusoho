define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
	require('jquery.plupload-queue-css');
	require('jquery.plupload-queue');
	require('plupload');
	// 这里require是有顺序要求的
	require('jquery.plupload-queue-zh-cn');

	exports.run = function() {
		var $container = $("#file-uploader-container"),
			targetType = $container.data('targetType'),
			uploadMode = $container.data('uploadMode')

		var extensions = '';
		if (targetType == 'courselesson') {
			if (uploadMode == 'cloud') {
				extensions = 'mp3,mp4,avi,flv,wmv';
			} else {
				extensions = 'mp3,mp4';
			}
		} else if (targetType == 'coursematerial') {
			extensions = 'jpg,jpeg,gif,png,txt,doc,docx,xls,xlsx,pdf,ppt,pptx,pps,ods,odp,mp4,mp3,avi,flv,wmv,wma,zip,rar,gz,tar,7z';
		}

		var filters = [];
		if (extensions.length > 0) {
			filters = [{
				title: "Files",
				extensions: extensions
			}];
		}

		var $div = $("#file-chooser-uploader-div");
		var divData = $div.data();

		var uploader = $div.pluploadQueue({
			runtimes: 'flash,html5,html4',
			max_file_size: '2gb',
			url: divData.uploadUrl,
			filters: filters,
			preinit: {
                Init: function (up, info) {
                	$("#file-chooser-uploader-div_container").removeAttr("title");
                }
            },			
			init: {
				FileUploaded: function(up, file, info) {
					response = $.parseJSON(info.response);
					if (divData.callback) {
						$.post(divData.callback, response, function(response) {
							if (divData.fileinfoUrl) {
								$.get($div.data('fileinfoUrl'), {
									key: response.hashId
								}, function(info) {}, 'json');
							}
						}, 'json');
					}

					if (up.total.uploaded == up.files.length) {
						$(".plupload_buttons").css("display", "inline");
						$(".plupload_upload_status").css("display", "inline");
						$(".plupload_start").addClass("plupload_disabled");
					}

				},

				QueueChanged: function(up){
					$(".plupload_start").removeClass("plupload_disabled");
				},

				Error: function(up, args) {
					Notify.danger('文件上传失败，请重试！');
				},
				UploadComplete: function(up, files) {
		            up.refresh();
		        },
				BeforeUpload: function(up, file) {
					var data = {};
					if (targetType == 'courselesson' && uploadMode == 'cloud') {
						data.convertor = 'video';
					}

					$.ajax({
						url: divData.paramsUrl,
						async: false,
						dataType: 'json',
						data: data,
						cache: false,
						success: function(response, status, jqXHR) {
							up.settings.url = response.url;
							up.settings.multipart_params = response.postParams;
							up.refresh();
						},
						error: function(jqXHR, status, error) {
							Notify.danger('请求上传授权码失败！');
							up.stop();
						}
					});
				}

			}

		});

		$('#modal').on('hide.bs.modal', function(e) {
			window.location.reload();
		});

	};

});