export default class StepOne {
  constructor($element) {
    this.$element = $element;
    this.init();
  }

  init() {
    this.initValidator();

    $('#create-certificate-template').on('click', () => {
      if (this.validator.form()) {
        $('#create-certificate-template').button('loading').addClass('disabled');
        this.$element.submit();
      }
    });
  }

  initValidator() {
    this.validator = this.$element.validate({
      rules: {
        name: {
          byte_maxlength: 60,
          required: {
            depends () {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
          course_title: true
        },
        targetType: {
          required: true,
        },
      },
    });
  }
}

new StepOne($('#certificate-template-form'));