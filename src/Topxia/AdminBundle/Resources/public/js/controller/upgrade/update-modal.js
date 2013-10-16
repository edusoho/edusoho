define(function(require, exports, module) {

	exports.run = function() {

		var checkEnvironmentUrl = $('#post-url').find('[data-type=check-environment]').data('url');
		var checkDependsUrl = $('#post-url').find('[data-type=check-depends]').data('url');
		var downloadExtractUrl = $('#post-url').find('[data-type=download-extract]').data('url');
		var checkLastErrorUrl = $('#post-url').find('[data-type=check-last-error]').data('url');
		var backupSystemUrl = $('#post-url').find('[data-type=backup-system]').data('url');
		var beginUpgradeUrl = $('#post-url').find('[data-type=begin-upgrade]').data('url');

		var postCheckDepends = function()
		{
			
			$.post(checkDependsUrl, function(checkDependsResponse) {
								
				$('#checking-depends').hide().show({
					duration: 3000
				});

				if (checkDependsResponse.status == 'error') {

					$("#check-depends-result").append('<h4 style="text-align: center">检查依赖关系未通过！</h4>')
							.append(checkDependsResponse.result)
							.append('请重新检查软件包的依赖关系之后，重新升级！')
							.hide().show({
								duration: 3000
							});

					return false;
				} 

				if (checkDependsResponse.status == 'ok'){

					$("#check-depends-result")
						.append("<h4 style='color:green; text-align: center'>检查依赖关系通过!</h4>")
						.hide().show({duration: 3000});

						$.post(downloadExtractUrl, function(downloadExtractResponse) {
								
								$('#downloading-extract').hide().show({
									duration: 4000
								});

								if (downloadExtractResponse.status == 'error') {

								$("#download-extract-result").append('<h4 style="text-align: center">下载并解压软件包失败！</h4>')
										.append(downloadExtractResponse.result)
										.append('请在正确配置环境之后，重新升级！')
										.hide().show({
											duration: 4000
										});

								return false;

								}


								if (downloadExtractResponse.status == 'ok') {

								$("#download-extract-result").append("<h4 style='color:green; text-align: center'>下载并解压软件包成功!")
									.hide().show({
										duration: 4000
									});


									$.post(backupSystemUrl, function(backupSystemResponse) {

										$('#backuping-system').hide().show({
											duration: 5000
										});

										if (backupSystemResponse.status == 'error') {

											$("#backup-system-result").append('<h4 style="text-align: center">备份系统失败！</h4>')
													.append(backupSystemResponse.result)
													.append('请在正确检查相应文件夹目录之后，重新升级！')
													.hide().show({
														duration: 5000
													});

											return false;

										}

										if(backupSystemResponse.status == 'ok'){
												
												$("#backup-system-result").append("<h4 style='color:green; text-align: center'>备份系统成功!")
												.hide().show({
													duration: 5000
												});

												$.post(beginUpgradeUrl, function(beginUpgradeResponse) {

														$('#begining-update').hide().show({
															duration: 6000
														});

														if (beginUpgradeResponse.status == 'error') {

															$("#begin-update-result").append('<h4 style="text-align: center">升级软件包失败！</h4>')
																	.append(beginUpgradeResponse.result)
																	.append('请在正确检查相应文件夹目录之后，重新升级！')
																	.hide().show({
																		duration: 6000
																	});

															return false;

														}

														if (beginUpgradeResponse.status == 'ok') {

															$("#begin-update-result").append("<h4 style='color:green; text-align: center'>升级软件包成功!")
																.hide().show({duration: 6000});

														}

												});

										}

									});

								}

						});

				}

			});

		}

		$('.modal-body').on('click', '#begin-update', function() {

			$(this).attr('disabled', true);

			$.post(checkLastErrorUrl, function(checkingLastErrorResponse) {

				$('#checking-last-error').hide().show({
					duration: 1000
				});

				if (checkingLastErrorResponse.status == 'error') {

					$("#check-last-error-result").append('<h4 style="text-align: center">上次操作存在错误!</h4>')
						.append(checkingLastErrorResponse.result)
						.append('请在系统回滚并恢复到正常版本之后，重新升级软件包！')
						.hide().show({
							duration: 1000
						});

					return false;

				} else {

					$("#check-last-error-result").append("<h4 style='color:green;text-align: center'>上次操作不存在错误!</h4>")
						.hide().show({
							duration: 1000
						});

				}

				if (checkingLastErrorResponse.status == 'ok') {

					$.post(checkEnvironmentUrl, function(checkEnvironmentResponse) {

						$('#checking-environment').hide().show({
							duration: 2000
						});

						if (checkEnvironmentResponse.status == 'error') {

							$("#check-environment-result").append('<h4 style="text-align: center">环境检测失败！</h4>')
									.append(checkEnvironmentResponse.result)
									.append('请重新检查相应目录的写操作权限之后，重新升级！')
									.hide().show({
										duration: 2000
									});

						return false;

						} else {

							$("#check-environment-result").append("<h4 style='color:green; text-align: center'>检查环境通过!</h4>")
								.hide().show({
									duration: 2000
								});

						}

						if (checkEnvironmentResponse.status == 'ok') {

							postCheckDepends();

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