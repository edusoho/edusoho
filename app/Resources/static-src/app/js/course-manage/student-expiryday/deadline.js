export default class Deadline {
  constructor(form) {
    this.$form = $(form);
    this.validator = null;
    this.init();
  }

  init() {
    this.initDatePicker('#deadline');
    this.initRadioChange();
    this.initSelectChange();
    this.initValidator();
    this.initUpdateType();
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
    let $modal = this.$form.parents('.modal');

    this.validator = this.$form.validate({
      rules: {
        day: {
          required: true,
          positive_integer: true,
          max: 7300,
          es_remote: {
            type: 'get',
            data: {
              waveType: function () {
                return $('[name=waveType]').val();
              },
              day: function () {
                return $('[name=day]').val();
              },
            }
          }
        }
      },
      messages: {
        day: {
          required: Translator.trans('validate.modify_days'),
          max: Translator.trans('validate.modify_day_number'),
          es_remote: Translator.trans('course_manage.student_expiryday_extend_error_hint_day'),
        }
      }
    });

    $('.js-save-deadline-set-form').click(() => {
      if (this.validator && this.validator.form()) {
        $.post(this.$form.attr('action'), this.$form.serialize(), function () {
          let user_name = $('#submit').data('user');
          cd.message({ type: 'success', message: Translator.trans('course_manage.student_expiryday_extend_success_hint', { name: user_name }) });
          $modal.modal('hide');
          window.location.reload();
        });
      }
    });
  }

  initUpdateType() {
    const updateType = $('[name="updateType"]:checked').val();
    const $deadline = $('[name="deadline"]');
    const $day = $('[name="day"]');
    this.elementRemoveRules($deadline);
    this.elementRemoveRules($day);
    switch (updateType) {
      case 'day':
        $deadline.prop('disabled', true).val('');
        this.elementAddRules($day, this.getDayRules());
        break;
      case 'date':
        $deadline.prop('disabled', false);
        $day.val(0);
        $('[name="waveType"]').val('plus');
        console.log(111);
        this.elementAddRules($deadline, this.getDateRules());
        break;
      default:
        break;
    }
  }

  initRadioChange() {
    $('input[name="updateType"]').on('change', () => {
      this.initUpdateType();
    });
  }

  initSelectChange() {
    const $waveType = $('[name="waveType"]');
    $waveType.on('change', (event) => {
      if (!this.validator.form()) {
        $(event.target).css('border-color', '#ed3e3e');
      }
    }).on('blur', (event) => {
      if (!this.validator.form()) {
        $(event.target).closest('.form-group').addClass('has-error');
      }
    });
    $('[name="day"]').on('blur', () => {
      const borderColor = this.validator.form() ? '#e1e1e1' : '';
      $waveType.css('border-color', borderColor);
    });
  }

  elementAddRules($element, options) {
    $element.rules('add', options);
  }

  elementRemoveRules($element) {
    $element.rules('remove');
    $element.removeClass('form-control-error');
    const $formGroup = $element.closest('.form-group');
    $formGroup.removeClass('has-error');
    $formGroup.find('.jq-validate-error').remove();
  }

  getDayRules() {
    return {
      required: true,
      positive_integer: true,
      es_remote: {
        type: 'get',
        data: {
          waveType: function () {
            return $('[name=waveType]').val();
          },
          day: function () {
            return $('[name=day]').val();
          },
        }
      },
      messages: {
        es_remote: Translator.trans('course_manage.student_expiryday_extend_error_hint_day'),
      }
    };
  }

  getDateRules() {
    return {
      required: true,
      date: true,
      es_remote: {
        type: 'get',
        data: {
          deadline: function () {
            return $('[name=deadline]').val();
          }
        }
      },
      messages: {
        es_remote: Translator.trans('course_manage.student_expiryday_extend_error_hint_date'),
        required: Translator.trans('validate.modify_date'),
      }
    };
  }
}