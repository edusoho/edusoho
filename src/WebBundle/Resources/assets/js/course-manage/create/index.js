class Creator{
	constructor() {
		this.init();
	}

	init(){
        $("#course-create-form").validate({
            onkeyup: false,
            rules: {
                title: {
                    required: true
                    // minlength:2,
                    // maxlength: 50,
                },
            },
            messages: {
                title: "请输入计划名称",
            },
            submitHandler: function(form){
            	$(form).ajaxSubmit();
            }
        });
	}
}

new Creator();