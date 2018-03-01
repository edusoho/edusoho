import notify from 'common/notify';

class Unlock {
	constructor() {
		this.init();		
	}

	init(){
		$('#courseSync-btn').click(function(){
			var $form = $('#courseSync-form');
		 	$.post($form.attr('action'), $form.serialize(), function(resp){
		 		console.log(resp);
		        if(resp.success){
		        	notify('success', Translator.trans('course_set.manage.unlock_success_hint'));
		        	$('#modal').modal('hide');
		        	location.reload();
		        }else{
		        	notify('danger', Translator.trans('course_set.manage.unlock_failure_hint')+ resp.message);
		        }
		    });
		});
	}
}

new Unlock();