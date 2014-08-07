define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
	var Widget = require('widget');
	require('jquery.plupload-queue-css');
	require('jquery.plupload-queue');
	require('plupload');
	// 这里require是有顺序要求的
	require('jquery.plupload-queue-zh-cn');

	var VideoQualitySwitcher = require('../widget/video-quality-switcher');

	exports.run = function() {
		var $container = $("#file-uploader-container"),
			targetType = $container.data('targetType'),
			uploadMode = $container.data('uploadMode'),
			hlsEncrypted = $container.data('hlsEncrypted');


		var switcher = null;
		if ($('.quality-switcher').length > 0) {
			var switcher = new VideoQualitySwitcher({
				element: '.quality-switcher'
			});
		}


		var extensions = '';
		if (targetType == 'courselesson') {
			if (uploadMode == 'cloud') {
				extensions = 'mp3,mp4,avi,flv,wmv,mov,ppt,pptx';
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