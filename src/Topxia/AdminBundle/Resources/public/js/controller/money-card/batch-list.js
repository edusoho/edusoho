define(function(require, exports, module) {

	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

        var $tableBatch = $('#money-card-batch-table');

        $tableBatch.on('click', '.lock-money-card-batch, .unlock-money-card-batch', function() {
            var $triggerBatch = $(this);

            if (!confirm('真的要' + $triggerBatch.attr('title') + '吗？')) {
                return ;
            }

            $.post($(this).data('url'), function(html){
                Notify.success($triggerBatch.attr('title') + '成功！');
                 var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function(){
                Notify.danger($triggerBatch.attr('title') + '失败');
            });
        });

        $tableBatch.on('click','.delete',function(){
            if (!confirm('真的要删除该批次充值卡吗？')) {
                return ;
            }
            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });

	};

});