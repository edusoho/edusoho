define(function (require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    exports.run = function () {
        var ids = [];
        $("[data-role='batch-item']:checked").each(function () {
            var id = $(this).parents('tr').attr('id');
            var userId = id.split('-').pop();
            if ($("#module").val() == "user" && userId == $("#appUserId").val()) {
                $(this).prop("checked", false);
                $(".js-user-help").removeClass("hidden");
                return;
            }
            ids.push(userId);
        });
        if(ids.length == 0 ){
            $("#batch-setting-org-btn").addClass("disabled")
        }
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
					$modal.modal('hide');
					Notify.success(Translator.trans('更新组织机构成功'));
					setTimeout(function(){
						window.location.reload();
					},1000);
				}).error(function() {
					Notify.danger(Translator.trans('操作失败'));
				});
			}
		});
	};

});