define(function(require, exports, module) {

  
	var Notify = require('common/bootstrap-notify');
	exports.run = function() {
		var $table=$('#thread-table');
		require('../../util/batch-select')($('#thread-table'));
		

		$('#openThread').on('click',function(){

			$('#status').attr('value','open');

		})
		$('#closeThread').on('click',function(){
			
			$('#status').attr('value','close');
		})


		$table.on('click','.delete-thread,.removeElite,.removeStick,.setElite,.setStick,.close-thread,.open-thread',function(){
			var $trigger = $(this);
			if(!confirm($(this).attr('title')+'吗？')){

			return ;

			}

			$.post($(this).data('url'),function(html){
			if(html=="success"){
			     Notify.success($trigger.attr('title')+ '成功！');
                 setTimeout(function(){window.location.reload();},1500); 
			}
			Notify.success($trigger.attr('title')+ '成功！');
			var $tr = $(html);
                $('#' + $tr.attr('id')).replaceWith(html);
			}).error(function(){
                Notify.danger($trigger.attr('title')+ '失败');
        	});
			
		})

	}
	
});