define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

    module.exports = function($element) {

        $element.on('click', '[data-role=batch-rename]', function() {
        	var $btn = $(this);
        		name = $btn.data('name');

            var ids = [];
            $element.find('[data-role=batch-item]:checked').each(function(){
                ids.push(this.value);
            });

            if (ids.length == 0) {
                Notify.danger('未选中任何' + name);
                return ;
            }

            $.get($btn.data('url'), {ids:ids}, function(response){
                $('#modal').html(response.html).modal('toggle');
            });

        });

    };

});