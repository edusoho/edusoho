import { TabChange } from '../help';

class Creator{
	constructor() {
		this.init();
	}

	init(){
		//init UI
		this._initDatePicker('#expiryStartDate');
		this._initDatePicker('#expiryEndDate');
        TabChange();

		
        let $form = $("#course-create-form");
        //init validator
        let validator = $form.validate({
            onkeyup: false,
            groups: {
                date: 'expiryStartDate expiryEndDate'
            },
            rules: {
                title: {
                    required: true
                },
                expiryDays: {
                    required: '#expiryByDays:checked',
                    digits:true
                },
                expiryStartDate: {
                    required: '#expiryByDate:checked',
                    date:true,
                    before: '#expiryEndDate'
                },
                expiryEndDate: {
                    required: '#expiryByDate:checked',
                    date:true,
                    after: '#expiryStartDate'
                }
            },
            messages: {
                title: Translator.trans('请输入教学计划课程标题'),
                expiryDays: Translator.trans('请输入学习有效期'),
                expiryStartDate: {
                    required: Translator.trans('请输入开始日期'),
                    before: Translator.trans('开始日期应早于结束日期')
                },
                expiryEndDate: {
                    required: Translator.trans('请输入结束日期'),
                    after: Translator.trans('结束日期应晚于开始日期')
                }
            }
        });

        $.validator.addMethod(
            "before",
            function(value, element, params) {
                // console.log(value, element, params, this.optional(element), $(params).val() > value);
                return this.optional(element) || $(params).val() > value;
            },
            Translator.trans('开始日期应早于结束日期')
        );

        $.validator.addMethod(
            "after",
            function(value, element, params) {
                // console.log(value, element, params, this.optional(element), $(params).val() < value);
                return this.optional(element) || $(params).val() < value;
            },
            Translator.trans('结束日期应晚于开始日期')
        );

        $('#course-submit').click(function(evt){
            if(validator.form()){
                $(evt.currentTarget).button('loading');
                $form.submit();
            }
        });
	}

	_initDatePicker($id)
	{
		let $picker = $($id);
        $picker.datetimepicker({
            format: 'yyyy-mm-dd',
            language:"zh",
            minView: 2, //month
            autoclose: true
        });
        $picker.datetimepicker('setStartDate',new Date());
	}
}

new Creator();