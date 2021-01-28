import { enterSubmit } from 'app/common/form';

class PlanTitle {
  constructor() {
    this.validator = null;
    this.init();
  }

  init() {
    this.initValidator();
  }

  initValidator() {
    const $form = $('#course-title-form');
    const $btn = $('#course-title-submit');
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
          maxlength: Translator.trans('course.manage.title_maxlength_error_hint'),
        }
      }
    });

    $btn.click((evt) => {
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
    enterSubmit($form, $btn);
  }

}

new PlanTitle();
