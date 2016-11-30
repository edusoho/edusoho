
class Marketing {
	constructor(){
		this.init();
	}

	init(){
		let $form = $('#course-marketing-form');

		let validator = $form.validate({
			onkeyup: false,
			rules: {
				price: {
					required: '#chargeMode:checked',
					digits: true
				},
				tryLookLength: {
					required: '#enableTryLook:checked',
					digits: true
				},
				tryLookLimit: {
					required: '#enableTryLook:checked',
					digits: true
				}
			},
			messages: {
				price: Translator.trans('请输入价格'),
				tryLookLength: Translator.trans('请输入试看时长'),
				tryLookLimit: Translator.trans('请输入视频观看时长限制')
			}
		});

		$('input[name="isFree"]').on('change', function(event){
            $('.js-is-free').toggle($('input[name="isFree"]:checked').val() == 0 ? 'show' : 'hide');
        });
        $('input[name="enableTryLook"]').on('change', function(event){
            $('.js-enable-try-look').toggle($('input[name="enableTryLook"]:checked').val() == 0 ? 'show' : 'hide');
        });

		$('#course-submit').click(function(evt){
            if(validator.form()){
                $(evt.currentTarget).button('loading');
                $form.submit();
            }
        });
	}
}


new Marketing();