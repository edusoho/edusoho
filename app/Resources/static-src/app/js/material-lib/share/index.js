const $from = $('#share-materials-form');

const $input = $('#target-teachers-input');

const data = $('#target-teachers-data').data('value');

$input.select2({
  multiple: true,
  data
});

$input.on('change', (data) => {
  $('.jq-validate-error').hide();
  $('.has-error').removeClass('has-error');
});

$from.validate({
  ajax: true,
  currentDom: '#form-submit',
  rules: {
    targetUserIds: {
      required: true,
      visible_character: true,
    }
  },
  messages: {
    targetUserIds: {
      required: Translator.trans('material.share.teacher_nickname_label'),
    }
  },
  submitSuccess() {
    $from.closest('.modal').modal('hide');
  }
});