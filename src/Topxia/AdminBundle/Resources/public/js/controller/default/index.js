define(function(require, exports, module) {
        
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

    	$('.tbody').on('click', 'button.remind-teachers', function(){
			$.post($(this).data('url'),function(response){
	        	Notify.success('提醒老师的私信，发送成功！');                
	    	});
	    });

    };

  });

