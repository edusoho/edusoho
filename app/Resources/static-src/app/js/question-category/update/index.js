class CategoryUpdate {
  constructor(options) {
    this.$element = $(options.element);
    this.validator();
  }

  validator() {
    let $element = this.$element;
    $element.validate({
      rules: {
        'name': {
          required: true,
          maxlength: 30,
          visible_character: true
        },
      },
      ajax: true,
      submitSuccess() {
        cd.message({ type: 'success', message: Translator.trans('question_bank.question_category.update_success') });
        window.location.reload();
      },
      submitError(response) {
        cd.message({ type: 'danger', message: Translator.trans(response.responseJSON.error.message) });
      }
    });
  }
}
  
new CategoryUpdate({
  element: '#category-form'
});