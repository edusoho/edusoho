class Creator
{
	constructor(props) {
        this.$element = props;
        this.$courseSetType = this.$element.find('.js-courseSetType');
        this.$currentCourseSetType = this.$element.find('.js-courseSetType.active');;
        this.init();
	}

	init(){
		this._extendValidator();
        console.log(this.$element);
		let validator = this.$element.validate({
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

        this.$courseSetType.click( event => {
            this.$courseSetType.removeClass('active');
            this.$currentCourseSetType = $(event.currentTarget);
            this.$currentCourseSetType.addClass('active');
            $('input[name="type"]').val( this.$currentCourseSetType.data('type'));
        });

        $('#courseset-create-btn').click(event => {
        	if(validator.form()){
        		$(event.currentTarget).button('loading');
        		this.$element.submit();	
        	}
        });
	}

	_extendValidator(){
        let $currentCourseSetType = this.$currentCourseSetType;
		$.validator.addMethod("open_live_course_title", function(value, element, params) {
	        if ($currentCourseSetType.data('type') === 'liveOpen' && !/^[^(<|>|'|"|&|‘|’|”|“)]*$/.test(value)) {
	            return false;
	        } else {
	            return true;
	        }
		}, Translator.trans('直播公开课标题暂不支持<、>、\"、&、‘、’、”、“字符'));		
	}
}

new Creator($('#courseset-create-form'));




