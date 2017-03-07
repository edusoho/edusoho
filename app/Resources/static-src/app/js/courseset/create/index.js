class Creator {
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
          open_live_course_title: () => {
            return this.$currentCourseSetType.data('type') === 'liveOpen';
          }
        }
      },
      messages: {
        title: {
          required: Translator.trans('请输入标题'),
          trim: Translator.trans('请输入标题'),
          open_live_course_title: Translator.trans('直播公开课标题暂不支持<、>、\"、&、‘、’、”、“字符'),
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

new Creator($('#courseset-create-form'));
