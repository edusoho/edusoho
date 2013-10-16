define(function(require, exports, module) {

	exports.run = function() {

		var checkEnvironmentUrl = $('#post-url').find('[data-type=check-environment]').data('url');
		var checkDependsUrl = $('#post-url').find('[data-type=check-depends]').data('url');
		var downloadExtractUrl = $('#post-url').find('[data-type=download-extract]').data('url');
		var beginInstallUrl = $('#post-url').find('[data-type=begin-install]').data('url');

		var postDonwloadExtract = function()
		{
			
			$.post(downloadExtractUrl, function(downloadExtractResponse) {
								
			$('#downloading-extract').hide().show({
				duration: 3000
			});

			if (downloadExtractResponse.status == 'error') {

				$("#download-extract-result").append('<h4 style="text-align: center">下载并解压软件包失败！</h4>')
						.append(downloadExtractResponse.result)
						.append('请在正确配置环境之后，重新安装！')
						.hide().show({
							duration: 3000
						});

				return false;

			}

				if (downloadExtractResponse.status == 'ok') {

					$("#download-extract-result").append("<h4 style='color:green; text-align: center'>下载并解压软件包成功!</h4><br>")
						.hide().show({
							duration: 3000
						});

						$.post(beginInstallUrl, function(beginInstallResponse) {

							$('#begining-install').hide().show({
								duration: 3000
							});

							if (beginInstallResponse.status == 'error') {

								$("#begining-install-result").append('<h4 style="text-align: center">安装软件包失败！</h4>')
										.append(beginInstallResponse.result)
										.append('请在正确配置环境之后，重新安装！')
										.hide().show({
											duration: 3000
										});

								return false;

							}

							if (beginInstallResponse.status == 'ok') {

								$("#begining-install-result").append("<h4 style='color:green; text-align: center'>安装软件包成功!</h4>")
									.hide().show({
										duration: 3000
								});

							}

						});

				}

			});

		}

		$('.modal-body').on('click', '#begin-install', function() {

			$(this).attr('disabled', true);

			$.post(checkEnvironmentUrl, function(checkEnvironmentResponse) {

				$('#checking-environment').hide().show({
					duration: 1000
				});

				if (checkEnvironmentResponse.status == 'error') {

					$("#check-environment-result").append('<h4 style="text-align: center">环境检测失败！</h4>')
						.append(checkEnvironmentResponse.result)
						.append('请重新检查相应目录的写操作权限之后，重新安装！')
						.hide().show({
							duration: 1000
						});

					return false;

				} else {

					$("#check-environment-result").append("<h4 style='color:green;text-align: center'>检查环境通过!</h4>")
						.hide().show({
							duration: 1000
						});

				}

				if (checkEnvironmentResponse.status == 'ok') {

					$.post(checkDependsUrl, function(checkDependsResponse) {

						$('#checking-depends').hide().show({
							duration: 2000
						});

						if (checkDependsResponse.status == 'error') {

							$("#check-depends-result").append('<h4 style="text-align: center">检查依赖关系未通过！</h4>')
									.append(checkDependsResponse.result)
									.append('请重新检查软件包的依赖关系之后，重新安装！')
									.hide().show({
										duration: 2000
									});

						return false;

						} else {

							$("#check-depends-result").append("<h4 style='color:green; text-align: center'>检查依赖关系通过!</h4>")
								.hide().show({
									duration: 2000
								});

						}

						if (checkDependsResponse.status == 'ok') {

							postDonwloadExtract();

						}

					});

				}

			});

		});

		
	  $('#modal').on('click', '.dismiss', function(e) {

  		window.location.reload();
      $("#modal").off('hide.bs.modal');

    });

	};

});