import notify from 'common/notify';

$('.js-start-exam').on('click', function(event){
	const endTime = ($('input[name="endTime"]').val()) * 1000
	if(endTime <= new Date().getTime()){
		event.preventDefault();
		notify('danger', Translator.trans('validate.endTime.validity'));
		setTimeout(function(){
			window,location.reload();
		},2000)
	}
})