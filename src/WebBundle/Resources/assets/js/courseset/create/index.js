class Creator
{
	constructor() {
		this.init();
	}

	init(){
		this._extendValidator();

		$("#course-info-form").validate({
            onkeyup: false,
            rules: {
                title: {
                    required: true,
                    open_live_course_title: true
                }
            },
            messages: {
                title: "请输入计划名称"
            },
            submitHandler: function(form){
            	$(form).submit();
            }
        });

        $('[data-toggle="tooltip"]').tooltip();

        $("#course-create-form .course-select").click(function (evt) {
            var $this = $(evt.target);
            if(!$this.hasClass('course-select')){
            	$this = $this.parent('.course-select');
            }
            var courseType = $this.data('type');
            $this.not('.disabled').addClass('active').parent().siblings().find('.course-select').removeClass('active');
            $('input[name="type"]').val(courseType);
        })
	}

	_extendValidator(){
		$.validator.addMethod("open_live_course_title", function(value, element, params) {
			console.log('value , element, params: ', value, element, params);
		    var $courseType = $("#course-create-form .course-select.active");
	        var courseType = $courseType.data('type');
	        var title = element.val();
	        if (courseType === 'liveOpen' && !/^[^(<|>|'|"|&|‘|’|”|“)]*$/.test(title)) {
	            // commit(false, Translator.trans('直播公开课标题暂不支持<、>、\"、&、‘、’、”、“字符'));
	            return false;
	        } else {
	            return true;
	        }
		}, "请正确输入课程标题");		
	}
}

new Creator();