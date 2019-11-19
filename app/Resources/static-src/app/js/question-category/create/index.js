
jQuery.validator.addMethod('fillCheck', function (value, element) {
  return this.optional(element) || /(\[\[(.+?)\]\])/i.test(value);
}, Translator.trans('course.question.create.fill_hint'));

jQuery.validator.addMethod('name_max', function (value, element) {
  let maxLength = true;
  let values = value.split('\n');
  values.map(function (string) {
    if (string.length > 30) {
      maxLength = false;
    }
  });
  return this.optional(element) || maxLength;
}, Translator.trans('question_bank.question_category.name_max_message'));

jQuery.validator.addMethod('name_chinese_alphanumeric', function (value, element) {
  let alphanumericValidator = true;
  let values = value.split('\n');
  let self = this;
  values.map(function (string) {
    alphanumericValidator = /^([\s]|[\u4E00-\uFA29]|[a-zA-Z0-9_.·])*$/i.test(string);
    if (alphanumericValidator == false) {
      return self.optional(element) || alphanumericValidator;
    }
  });
  return self.optional(element) || alphanumericValidator;
}, Translator.trans('question_bank.question_category.name_chinese_alphanumeric_message'));

class CategoryCreate {
  constructor(options) {
    this.$element = $(options.element);
    this.validator();
  }

  validator() {
    let $element = this.$element;
    $element.validate({
      rules: {
        'categoryNames': {
          required: true,
          name_max: true,
          name_chinese_alphanumeric: true
        },
      },
      ajax: true,
      submitSuccess() {
        cd.message({ type: 'success', message: Translator.trans('question_bank.question_category.create_success') });
        window.location.reload();
      },
      submitError(response) {
        cd.message({ type: 'danger', message: Translator.trans(response.responseJSON.error.message) });
      }
    });
  }
}
  
new CategoryCreate({
  element: '#category-form'
});