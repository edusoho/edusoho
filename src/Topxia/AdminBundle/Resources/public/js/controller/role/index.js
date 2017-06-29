define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
    	$('.js-delete-role').click(function(){
    		var url = $(this).data('url');

			if(!confirm(Translator.trans('确认删除该角色吗?'))){
				return;
			}

    		$.post(url).done(function(){
				Notify.success(Translator.trans('删除角色成功'));
				document.location.reload();
			}).fail(function (error) {
				Notify.danger(Translator.trans('删除该角色失败'));
			});
    	})

    }
})