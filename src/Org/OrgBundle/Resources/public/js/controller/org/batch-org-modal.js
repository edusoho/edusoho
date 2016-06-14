define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
	exports.run = function() {
		var ids = [];
		$("[data-role='batch-item']:checked").each(function() {
			var id = $(this).parents('tr').attr('id');
			ids.push(id.split('-').pop());
		});
		$("#batch-ids").val(ids);


		var $modal = $('#batch-setting-org-form').parents('.modal');
		var validator = new Validator({
			element: '#batch-setting-org-form',
			autoSubmit: false,
			onFormValidated: function(error, results, $form) {
				if (error) {
					return false;
				}
				$.post($form.attr('action'), $form.serialize(), function(result) {
					console.log(1);
					$modal.modal('hide');
					Notify.success('更新组织机构成功');
					setTimeout(function(){
						window.location.reload();
					},1000);
				}).error(function() {
					Notify.danger('操作失败');
				});
			}
		});
	};



});