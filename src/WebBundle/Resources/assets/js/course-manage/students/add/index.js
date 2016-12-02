
class StudentAdd {
	constructor(){
		this.init();
	}

	init(){
		let $form = $('#student-add-form');
		let validator = $form.validate({
			onkeyup: false,
			rules: {
				queryfield: {
					required: true,
					remote: {
						url: $('#student-nickname').data('url'),
						type: 'get',
						data: {
							'value': function(){
								return $('#student-nickname').val();
							}
						}	
					}
				}
			},
			messages: {
				queryfield: {
					remote : Translator.trans('请输入学员邮箱/手机号/用户名')
				}
			}
		});

		$('#student-add-submit').click(function(event){
			if(validator.form()){
				$(event.target).button('loading');
				$form.submit();
			}
		});
	}
}

new StudentAdd();