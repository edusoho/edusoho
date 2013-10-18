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
			$("#step2-process").append("开始系统依赖包检查......");
			$.post(checkDependsUrl, function(checkDependsResponse) {
				if (checkDependsResponse.status == 'error') {
					textResult = $("#step2-result").attr('style','color:#FF0000').append('系统依赖包检查失败！ <br/>');
					for(var i=0; i<checkDependsResponse.result.length;i++){
						textResult = textResult.append(checkDependsResponse.result[i]+'<br/>');
					}
					$("#step6").append("系统更新失败！");
					return false;
				} else {
					textResult = $("#step2-result").attr('style','color:#096').append('系统依赖包检查OK！');
				}

				$("#step3-process").append("开始下载、解压缩更新包......");
				$.post(downloadExtractUrl, function(downloadExtractResponse) {
					if (downloadExtractResponse.status == 'error') {
						textResult = $("#step3-result").attr('style','color:#FF0000').append('下载、解压缩更新包失败！ <br/>');
						for(var i=0; i<downloadExtractResponse.result.length;i++){
							textResult = textResult.append(downloadExtractResponse.result[i]+'<br/>');
						}
						$("#step6").append("系统更新失败！");
						return false;
					} else {
						textResult = $("#step3-result").attr('style','color:#096').append('下载、解压缩更新包OK！');
					}
					$("#step4-process").append("开始备份系统、数据库......");
					$.post(backupSystemUrl, function(backupSystemResponse) {
						if (backupSystemResponse.status == 'error') {
							textResult = $("#step4-result").attr('style','color:#FF0000').append('备份系统、数据库失败！ <br/>');
							for(var i=0; i<backupSystemResponse.result.length;i++){
								textResult = textResult.append(backupSystemResponse.result[i]+'<br/>');
							}
							$("#step6").append("系统更新失败！");
							return false;
						} else {
							textResult = $("#step4-result").attr('style','color:#096').append('备份系统、数据库OK！');
						}
						$("#step5-process").append("更新数据以及系统文件......");
						$.post(beginUpgradeUrl, function(beginUpgradeResponse) {
								if (beginUpgradeResponse.status == 'error') {
									textResult = $("#step5-result").attr('style','color:#FF0000').append('更新数据以及系统文件失败！ <br/>');
									for(var i=0; i<beginUpgradeResponse.result.length;i++){
										textResult = textResult.append(beginUpgradeResponse.result[i]+'<br/>');
									}
									$("#step6").append("系统更新失败！");
									return false;
								} else {
									textResult = $("#step5-result").attr('style','color:#096').append('更新数据以及系统文件OK！');
									$("#step6").append("系统更新成功！");
								}
						});
					});
				});
			});

		}

		$('.modal-body').on('click', '#begin-update', function() {
			$(this).attr('disabled', true);
			$.post(checkLastErrorUrl, function(checkingLastErrorResponse) {
				if (checkingLastErrorResponse.status == 'error') {
					if(!confirm('上次更新系统需回滚，继续安装可能会发生不可预料的错误，您确定继续吗？')){
						return false;
					}
				} 
				$("#step1-process").append("开始检查环境......");
				$.post(checkEnvironmentUrl, function(checkEnvironmentResponse) {
					if (checkEnvironmentResponse.status == 'error') {
						textResult = $("#step1-result").attr('style','color:#FF0000').append('环境检测失败！ <br/>');
						for(var i=0; i<checkEnvironmentResponse.result.length;i++){
    						textResult = textResult.append(checkEnvironmentResponse.result[i]+'<br/>');
						}
						$("#step6").append("系统更新失败！");
						return false;
					} else {
						textResult = $("#step1-result").attr('style','color:#096').append('环境检测OK！');
					}
					return postCheckDepends();

				});


			});

		});

		
	  $('#modal').on('click', '.dismiss', function(e) {

  		window.location.reload();
      $("#modal").off('hide.bs.modal');

    });

	};

});