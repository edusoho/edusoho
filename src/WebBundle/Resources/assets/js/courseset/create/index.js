class Creator
{
	constructor() {
		this.init();
	}

	init(){
		this._extendValidator();
		let $form = $("#courseset-create-form");
		let validator = $form.validate({
            onkeyup: false,
            rules: {
                title: {
                    required: true,
                    open_live_course_title: true
                }
            },
            messages: {
                title: "请输入有效的课程标题（直播公开课标题暂不支持<、>、\"、&、‘、’、”、“字符）"
            }
        });

        $('[data-toggle="tooltip"]').tooltip();

        $("#courseset-create-form .course-select").click(function (evt) {
            let $this = $(evt.target);
            if(!$this.hasClass('course-select')){
            	$this = $this.parent('.course-select');
            }
            let courseType = $this.data('type');
            $this.not('.disabled').addClass('active').parent().siblings().find('.course-select').removeClass('active');
            $('input[name="type"]').val(courseType);
        });

        $('#courseset-create-btn').click(function(evt){
        	console.log('#courseset-create-form : submit');
        	if(validator.form()){
        		$(evt.target).button('loading');
        		$form.submit();	
        	}
        });
	}

	_extendValidator(){
		$.validator.addMethod("open_live_course_title", function(value, element, params) {
			console.log('value , element, params: ', value, element, params);
		    let $courseType = $("#courseset-create-form .course-select.active");
	        let courseType = $courseType.data('type');
	        let title = value;
	        console.log('courseType : ', courseType);
	        if (courseType === 'liveOpen' && !/^[^(<|>|'|"|&|‘|’|”|“)]*$/.test(title)) {
	            // commit(false, Translator.trans('直播公开课标题暂不支持<、>、\"、&、‘、’、”、“字符'));
	            return false;
	        } else {
	            return true;
	        }
		}, Translator.trans('直播公开课标题暂不支持<、>、\"、&、‘、’、”、“字符'));		
	}
}

new Creator();