export default class Create {
  constructor($element) {
    this.$element = $element;
    this.$courseSetType = this.$element.find('.js-courseSetType');
    this.$currentCourseSetType = this.$element.find('.js-courseSetType.active');;
    this.init();
  }

  init() {
    this.validator = this.$element.validate({
      rules: {
        title: {
          maxlength: 100,
          required: true,
          trim: true,
          course_title: true,
        }
      },
      messages: {
        title: {
          required: Translator.trans('请输入标题'),
          trim: Translator.trans('请输入标题'),
        }
      }
    });

    this.$courseSetType.click(event => {
      this.$courseSetType.removeClass('active');
      this.$currentCourseSetType = $(event.currentTarget).addClass('active');
      $('input[name="type"]').val(this.$currentCourseSetType.data('type'));
      let $title = $('#course_title');
       $title.rules('remove');
      if (this.$currentCourseSetType.data('type') != 'live') {
        $title.rules("add", {
          required: true,
          trim: true,
          course_title: true,
        });
      }else {
        $title.rules("add", {
          required: true,
          trim: true,
          open_live_course_title: true,
        });
      }
    });
  }
}