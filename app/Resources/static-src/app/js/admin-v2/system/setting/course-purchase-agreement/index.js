export default class CoursePurchaseAgreement {
  constructor($element) {
    this.$element = $element;
    this.init();
  }

  init() {
    this.initValidator();

    $('#course-purchase-agreement').on('click', () => {
      if (this.validator.form()) {
        $('#course-purchase-agreement').button('loading').addClass('disabled');
        this.$element.submit();
      }
    });
  }

  initValidator() {
    this.validator = this.$element.validate({
      rules: {
        purchaseAgreementTitle: {
          required: true,
        },
        purchaseAgreementContent: {
          required: true,
        },
      },
    });
  }
}

new CoursePurchaseAgreement($('#course-purchase-agreement-form'));