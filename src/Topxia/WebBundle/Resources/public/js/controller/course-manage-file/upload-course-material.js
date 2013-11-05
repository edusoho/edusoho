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
			runtimes: 'flash',
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

				UploadProgress: function(up, file) {

					$('#modal').on('hide.bs.modal', function(e) {

						if (file.percent < 100) {

							if (!confirm('退出对话框会中断正在上传中的文件，是否继续？')) {
								$("#modal").off('hide.bs.modal');
								return false;
							}

							up.stop();
							$("#modal").off('hide.bs.modal');

						}

					});

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

		$('form').submit(function(e) {
			var uploader = $('#file-chooser-uploader-div').pluploadQueue();
			if (uploader.total.uploaded == 0) {
				if (uploader.files.length > 0) {
					uploader.bind('UploadProgress', function() {
						if (uploader.total.uploaded == uploader.files.length)
							$('form').submit();
					});
					uploader.start();
				} else
					alert('你必须至少上传一个文件！');
				e.preventDefault();
			}
		});

		uploader.init();

	};

});