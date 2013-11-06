define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
	require('jquery.plupload-queue-css');
	require('jquery.plupload-queue');
	require('plupload');
	// 这里require是有顺序要求的
	require('jquery.plupload-queue-zh-cn');

	exports.run = function() {
		$div = $("#file-chooser-uploader-div");
		var divData = $div.data();

		var uploader = $div.pluploadQueue({
			runtimes: 'html5,flash,html4,silverlight',
			max_file_size: '2gb',
			url: divData.uploadUrl,
			resize: {
				width: 500,
				height: 500,
				quality: 90
			},
			init: {

				FileUploaded: function(up, file, info) {

					response = $.parseJSON(info.response);
					if (divData.callback) {
						$.post(divData.callback, response, function(response) {
							Notify.success(file.name + '文件上传成功！');
							if (divData.fileinfoUrl) {
								$.get($div.data('fileinfoUrl'), {
									key: response.hashId
								}, function(info) {}, 'json');
							}
						}, 'json');
					} else {
						Notify.success(file.name + '文件上传成功！');
					}
				},

				Error: function(up, args) {
					Notify.danger('文件上传失败，请重试！');
				},

				BeforeUpload: function(up, file) {
					$.ajax({
						url: divData.paramsUrl,
						async: false,
						dataType: 'json',
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

		uploader.init();

		$('#modal').on('hide.bs.modal', function(e) {
            window.location.reload();
		});

	};

});