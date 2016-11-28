import { TabChange, publishCourse } from '../help';

class InfoEditor {
	constructor() {
		this.init();
	}

	init(){
		//init UI
		this._initDatePicker('#expiryStartDate');
		this._initDatePicker('#expiryEndDate');
        TabChange();
        publishCourse();

		CKEDITOR.replace('summary', {
		  allowedContent: true,
		  toolbar: 'Detail',
		  filebrowserImageUploadUrl: $('#summary').data('imageUploadUrl')
		});

        let $form = $('#course-info-form');
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
                    before: ['#expiryByDate', '#expiryEndDate']
                },
                expiryEndDate: {
                    required: '#expiryByDate:checked',
                    date:true,
                    after: ['#expiryByDate', '#expiryStartDate']
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
                if(!$(params[0]).checked){
                    return true;
                }
                // console.log(value, element, params, this.optional(element), $(params).val() > value);
                return this.optional(element) || $(params[1]).val() > value;
            },
            Translator.trans('开始日期应早于结束日期')
        );

        $.validator.addMethod(
            "after",
            function(value, element, params) {
                if(!$(params[0]).checked){
                    return true;
                }
                // console.log(value, element, params, this.optional(element), $(params).val() < value);
                return this.optional(element) || $(params[1]).val() < value;
            },
            Translator.trans('结束日期应晚于开始日期')
        );

        $('#course-submit').click(function(evt){
            console.log('submit', validator.form());
            if(validator.form()){
                console.log('validated');
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

new InfoEditor();