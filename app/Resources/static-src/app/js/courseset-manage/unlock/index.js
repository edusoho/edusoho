import notify from 'common/notify';

class Unlock {
	constructor() {
		this.init();		
	}

	init(){
		$('#courseSync-btn').click(function(){
			var $form = $("#courseSync-form");
		 	$.post($form.attr('action'), $form.serialize(), function(resp){
		 		console.log(resp);
		        if(resp.success){
		        	notify('success', '解除同步成功！');
		        	$('#modal').modal('hide');
		        	location.reload();
		        }else{
		        	notify('danger', '解除同步失败：'+ resp.message);
		        }
		    });
		});
	}
}

new Unlock();