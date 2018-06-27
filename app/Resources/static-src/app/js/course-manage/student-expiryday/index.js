import notify from 'common/notify';

class Deadline {
  constructor() {
    this.validator = null;
    this.init();
  }
  init() {
    this.initValidator();
    this.initDatePicker('#deadline');
    this.initUpdateType();
    this.initRadioChange();
  }

  initDatePicker($id) {
    let $picker = $($id);
    $picker.datetimepicker({
      format: 'yyyy-mm-dd',
      language: document.documentElement.lang,
      minView: 2, //month
      autoclose: true,
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    }).on('hide', () => {
      this.validator.form();
    });
    $picker.datetimepicker('setStartDate', new Date());
  }

  initValidator() {
    let $modal = $('#deadline-set-form').parents('.modal');
    let $form = $('#deadline-set-form');

    this.validator = $form.validate({
      rules: {
        day: {
          positive_integer: true,
        }
      }
    });

    $('.js-save-deadline-set-form').click(() => {
      if (this.validator.form()) {
        $.post($form.attr('action'), $form.serialize(), function () {
          let user_name = $('#submit').data('user');
          notify('success',Translator.trans('course_manage.student_expiryday_extend_success_hint', { name: user_name }));
          $modal.modal('hide');
          window.location.reload();
        }).error(function () {
          let user_name = $('#submit').data('user');
          notify('danger',Translator.trans('course_manage.student_expiryday_extend_failed_hint', { name: user_name }));
        });
      }
    });
  }

  initUpdateType() {
    let updateType = $('[name="updateType"]:checked').val();
    let $deadline = $('[name="deadline"]');
    let $day = $('[name="day"]');
    this.elementRemoveRules($deadline);
    this.elementRemoveRules($day);
    switch (updateType) {
    case 'day':
      this.elementAddRules($day, this.getDayRules());
      this.validator.form();
      break;
    case 'date':
      this.elementAddRules($deadline, this.getDateRules());
      this.validator.form();
      break;
    default:
      this.validator.form();
      break;
    }
  }

  initRadioChange() {
    $('input[name="updateType"]').on('change', (event) => {
      this.initUpdateType();
    });
  }

  elementAddRules($element, options) {
    $element.rules('add', options);
  }

  elementRemoveRules($element) {
    $element.rules('remove');
  }

  getDayRules() {
    return {
      positive_integer: true,
    };
  }

  getDateRules() {
    return {
      required: true,
      date: true,
    };
  }
}
new Deadline();