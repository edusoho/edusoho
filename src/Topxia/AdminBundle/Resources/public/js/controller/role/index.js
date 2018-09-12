define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
    	$('.js-delete-role').click(function(){
    		var url = $(this).data('url');

			if(!confirm(Translator.trans('admin.role.delete_hint'))){
				return;
			}

    		$.post(url).done(function(){
				Notify.success(Translator.trans('admin.role.delete_success_hint'));
				document.location.reload();
			}).fail(function (error) {
				Notify.danger(Translator.trans('admin.role.delete_fail_hint'));
			});
    	})

    }
})