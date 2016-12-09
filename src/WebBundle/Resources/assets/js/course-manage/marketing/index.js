import { TabChange } from '../help';

class Marketing {
	constructor(){
		this.init();
	}

	init(){
		let $form = $('#course-marketing-form');
		TabChange();

		let validator = $form.validate({
			onkeyup: false,
			rules: {
				price: {
					required: '#chargeMode:checked',
					currency: true
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
				price: {
					required: Translator.trans('请输入价格'),
					currency: Translator.trans('请输入价格，最多两位小数')
				},
				tryLookLength: Translator.trans('请输入试看时长'),
				tryLookLimit: Translator.trans('请输入视频观看时长限制')
			}
		});

		$.validator.addMethod(
            "currency",
            function(value, element, params) {
                return this.optional(element) || /^\d{0,8}(\.\d{0,2})?$/.test(value);
            },
            Translator.trans('请输入价格，最多两位小数')
        );

		$('input[name="isFree"]').on('change', function(event){
			if($('input[name="isFree"]:checked').val() == 0){
				$('.js-is-free').removeClass('hidden');
			}else{
				$('.js-is-free').addClass('hidden');
			}
        });
        $('input[name="tryLookable"]').on('change', function(event){
        	if($('input[name="tryLookable"]:checked').val() == 1){
				$('.js-enable-try-look').removeClass('hidden');
			}else{
				$('.js-enable-try-look').addClass('hidden');
			}
            // $('.js-enable-try-look').toggle($('input[name="tryLookable"]:checked').val() == 0 ? 'show' : 'hide');
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