define(function(require, exports, module) {

    require("jquery.bootstrap-datetimepicker");
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {

		var $tableCard = $('#money-card-table');

		$tableCard.on('click', '.lock-money-card, .unlock-money-card', function() {
			var $triggerCard = $(this);

			if (!confirm('真的要' + $triggerCard.attr('title') + '吗？')) {
				return ;
			}

            $.post($(this).data('url'), function(html){
                Notify.success($triggerCard.attr('title') + '成功！');
                 var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith($tr);
            }).error(function(){
                Notify.danger($triggerCard.attr('title') + '失败');
            });
		});

        $tableCard.on('click','.delete',function(){
            if (!confirm('真的要删除该充值卡吗？')) {
                return ;
            }
            $.post($(this).data('url'), function(){
                window.location.reload();
            });
        });

        $("#deadlineSearch").datetimepicker({
            language: 'zh-CN',
            autoclose: true,
            format: 'yyyy-mm-dd',
            minView: 'month'
        });

	};

});