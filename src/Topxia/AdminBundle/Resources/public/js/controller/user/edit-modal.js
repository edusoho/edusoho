define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');
	exports.run = function() {

        var $modal = $('#user-edit-form').parents('.modal');

        var validator = new Validator({
            element: '#user-edit-form',
            autoSubmit: false,
            onFormValidated: function(error, results, $form) {
            	if (error) {
            		return false;
            	}

				$.post($form.attr('action'), $form.serialize(), function(html) {
					$modal.modal('hide');
					Notify.success('用户信息保存成功');
					var $tr = $(html);
					$('#' + $tr.attr('id')).replaceWith($tr);
				}).error(function(){
					Notify.danger('操作失败');
				});
            }
        });

	};

});