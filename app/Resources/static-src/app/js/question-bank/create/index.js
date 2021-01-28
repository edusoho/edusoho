let $form = $('#bank-form');

$form.validate({
  currentDom: '#create-btn',
  ajax: true,
  rules: {
    name: {
      required: true,
      maxlength: 30,
      trim: true
    },
    categoryId: {
      required: true,
    },
  },
  messages: {
    categoryId: {
      required: Translator.trans('admin.question_bank.choose_category')
    }
  },
  submitSuccess(response) {
    window.location.href = response.goto;
  },
});

$('[name="categoryId"]').select2({
  treeview: true,
  dropdownAutoWidth: true,
  treeviewInitState: 'collapsed',
  placeholderOption: 'first',
  formatNoMatches: function() {
    return Translator.trans('admin.question_bank.no_category');
  }
});