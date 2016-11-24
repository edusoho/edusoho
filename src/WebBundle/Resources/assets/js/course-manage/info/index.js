import { TabChange } from '../help';

class InfoEditor {
	constructor() {
		this.init();
	}

	init(){
		//init UI
		this._initDatePicker('#expiryStartDate');
		this._initDatePicker('#expiryEndDate');
        TabChange();

		CKEDITOR.replace('summary', {
		  allowedContent: true,
		  toolbar: 'Detail',
		  filebrowserImageUploadUrl: $('#summary').data('imageUploadUrl')
		});

		//init validator
        $("#course-info-form").validate({
            onkeyup: false,
            rules: {
                title: {
                    required: true
                    // minlength:2,
                    // maxlength: 50,
                },
                expiryDays: {
                	range: [0, 1000000],
                	digits: true
                }
            },
            messages: {
                title: "请输入计划名称",
                expiryDays: '请输入0或正整数'
            },
            submitHandler: function(form){
            	// $(form).ajaxSubmit();
            	// $('#course-submit').text($('#course-submit').data('submiting-text'));
            	$(form).submit();
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