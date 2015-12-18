define(function(require, exports, module) {

	var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);
    var DraggableWidget = require('../marker/mange');

    var myDraggableWidget = new DraggableWidget ({
        element:"#lesson-dashboard"
    })
    exports.run = function() {
		$form = $('.mark-from');
		var validator = new Validator({
      		element: $form,
      		autoSubmit: false,
      		autoFocus: false,
          	onFormValidated: function(error, results, $form) {
	          	if (error) {
	                return ;
	           	}
	          	$.post($form.attr('action'), $form.serialize(), function(response){
	             	$('.question').html(response);
	          	});	
    
        	}
		});

		$(".pagination a").on('click',function(e){
			e.preventDefault();
			$.get($(this).attr('href'),function(response){
        		$('.question').html(response);
        	})
		})

		$(".question-tr").on('click','.marker-preview',function(){
			$.get($(this).data('url'),function(response){
				$('.modal').modal('show');
				$('.modal').html(response);
				
			})
		})

        

    }
});