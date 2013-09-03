define(function(require, exports, module) {
        
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

    	$('.tbody').on('click', 'button.remind-teachers', function(){
			$.post($(this).data('url'),function(response){
	       Notify.success('提醒老师的通知，发送成功！');                
	    	});
	    });

    	$('.row').on('change', '#date-type-select',function(){
    		$.get($(this).data('url'),{dateType: this.value }, function(response){
          if(response.status == 'ok'){
	    		 $('#welcomed-courses').replaceWith(response.html)
          }
    		}, 'json');
    	});

    };

  });

