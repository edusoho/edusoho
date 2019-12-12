
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

jQuery.validator.addMethod('name_visible_character', function (value, element) {
  let visibleValidator = true;
  let values = value.split('\n');
  let self = this;
  $.each(values, function (key, string) {
    visibleValidator = ($.trim(string).length > 0);
    if (visibleValidator == false) {
      return self.optional(element) || visibleValidator;
    }
  });
  return self.optional(element) || visibleValidator;
}, Translator.trans('validate.visible_character.message'));

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
          name_visible_character: true
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