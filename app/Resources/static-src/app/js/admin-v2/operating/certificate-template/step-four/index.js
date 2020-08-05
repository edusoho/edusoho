export default class StepFour {
  constructor($element) {
    this.$element = $element;
    this.init();
  }

  init() {
    this.initValidator();
    this.initEvent();
  }

  initValidator() {
    this.validator = this.$element.validate({
      rules: {
        certificateName: {
          maxlength: 50,
        },
        recipientContent: {
          maxlength: 50,
        },
        certificateContent: {
          maxlength: 300,
        },
      },
    });
  }

  initEvent() {
    $('.es-switch').on('click' , function () {
      let $input = $(this).find('.es-switch__input');
      if ($input.attr('name') !== 'qrCodeSet') {
        return;
      }
      let ToggleVal = parseInt($input.val()) === parseInt($input.data('open')) ? $input.data('close') : $input.data('open');
      $input.val(ToggleVal);
      $(this).toggleClass('is-active');
    });

    $('#update-certificate-template').on('click', () => {
      if (this.validator.form()) {
        $('#update-certificate-template').button('loading').addClass('disabled');
        this.$element.submit();
      }
    });
  }
}

new StepFour($('#certificate-template-form'));
