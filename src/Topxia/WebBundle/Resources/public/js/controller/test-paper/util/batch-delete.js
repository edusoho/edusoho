define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');
    Test = require('./util');

    module.exports = function($element) {

        $element.on('click', '[data-role=batch-delete]', function() {
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

            if (!confirm('这的要删除选中的' + ids.length + '条' + name + '吗？')) {
                return ;
            }

            $element.find('[data-role=batch-item]:checked').each(function(){
                var $item = $(this).parents('.questionType');
                if($item.data('type') == 'material'){
                    $('#test-item-table').find('[data-type='+$item.attr('id')+']').remove();
                }
                $(this).parents('[data-role=item]').remove();
            });

            Notify.success('删除' + name + '成功');

            Test.util();

        });

    };

});