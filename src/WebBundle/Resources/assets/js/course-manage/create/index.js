class Creator{
	constructor() {
		this.init();
	}

	init(){
		//init UI
		this._initDatePicker('#expiryStartDate');
		this._initDatePicker('#expiryEndDate');

		//init validator
        $("#course-create-form").validate({
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
            	$(form).ajaxSubmit();
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