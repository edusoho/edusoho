class PlanTitle {
  constructor() {
    this.validator = null;
    this.init();
  }

  init() {
    this.initValidator();
  }

  initValidator() {
    let $form = $('#course-title-form');
    this.validator = $form.validate({
      rules: {
        title: {
          required: true,
          trim: true,
          maxlength: 10,
        }
      },
      messages: {
        title: {
          required: Translator.trans('course.manage.title_required_error_hint'),
          maxlength: Translator.trans('course.manage.title_maxlength_error_hint'),
        }
      }
    });

    $('#course-title-submit').click((evt) => {
      if (this.validator.form()) {
        $(evt.currentTarget).button('loading');
        let params = { title: $('#planTitle').val() };
        $.post($form.attr('action'), params, resp => {
          if (resp && resp.success) {
            location.reload();
          }
        });
      }
    });
  }
}

new PlanTitle();