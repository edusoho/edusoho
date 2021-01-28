define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

    module.exports = function($element) {
        
        $element.on('click', '[data-role=batch-delete]', function() {
            
        	var $btn = $(this);
        		name = $btn.data('name');

            var ids = [];
            $element.find('[data-role=batch-item]:checked').each(function(){
                ids.push(this.value);
            });

            if (ids.length == 0) {
                Notify.danger(Translator.trans('admin.util.batch_delete.checked_empty_hint',{name:name}));
                return ;
            }

            if (!confirm(Translator.trans('admin.util.batch_delete.delete_hint',{ids:ids.length,name:name}))) {
                return ;
            }

            $element.find('.btn').addClass('disabled');

            Notify.info(Translator.trans('admin.util.batch_delete.deleting_hint',{name:name}), 60);

            $.post($btn.data('url'), {ids:ids}, function(){
            	window.location.reload();
            });

        });

    };

});