export default class Create {
  constructor($element) {
    this.$element = $element;
    this.$courseSetType = this.$element.find('.js-courseSetType');
    this.$currentCourseSetType = this.$element.find('.js-courseSetType.active');
    this.init();
  }

  init() {
    this.validator = this.$element.validate({
      rules: {
        title: {
          byte_maxlength: 200,
          required: true,
          trim: true,
          course_title: true,
        }
      },
      messages: {
        title: {
          required: Translator.trans('course_set.title_required_error_hint'),
          trim: Translator.trans('course_set.title_required_error_hint'),
        }
      }
    });

    this.$courseSetType.click(event => {
      this.$courseSetType.removeClass('active');
      this.$currentCourseSetType = $(event.currentTarget).addClass('active');
      let type = this.$currentCourseSetType.data('type');
      $('input[name="type"]').val(type);
      let $title = $('#course_title');
      if (type === 'live') {
        $title.rules('remove');
        $title.rules('add', {
          required: true,
          byte_maxlength: 200,
          trim: true,
          open_live_course_title: true,
        });
      }
    });
  }
}