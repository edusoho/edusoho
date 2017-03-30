export default  class Create {
  constructor($element) {
    this.$element = $element;
    this.$courseSetType = this.$element.find('.js-courseSetType');
    this.$currentCourseSetType = this.$element.find('.js-courseSetType.active');;
    this.init();
  }

  init() {
    let validator = this.$element.validate({
      currentDom: '#courseset-create-btn',
      rules: {
        title: {
          required: true,
          trim: true,
          open_live_course_title: true,
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
    });

    $('#courseset-create-btn').click(event => {
      if (validator.form()) {
        this.$element.submit();
      }
    });
  }
}