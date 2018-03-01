var $form = $('#reset-email-form');
let $btn = $('#next-btn');
let validator = $form.validate({
	rules: {
		email: {
			required: true,
			es_email: true,
			es_remote: {
				type: 'get'
			}
		},
		password: {
			required: true,
			minlength: 5,
			maxlength: 20
		}
	}
});

$btn.click(()=>{
	if(validator.form()) {
		$btn.button('loadding');
		$form.submit();
	}
});