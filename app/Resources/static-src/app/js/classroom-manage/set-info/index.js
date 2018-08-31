import 'app/js/classroom-manage/classroom-create';

initEditor();
const validator = initValidator();
toggleExpiryValue($('[name=expiryMode]:checked').val());

$('[name=\'expiryMode\']').change(function () {
  if (app.arguments.classroomStatus === 'published') {
    return false;
  }
  var expiryValue = $('[name=\'expiryValue\']').val();
  if (expiryValue) {
    if (expiryValue.match('-')) {
      $('[name=\'expiryValue\']').data('date', $('[name=\'expiryValue\']').val());
    } else {
      $('[name=\'expiryValue\']').data('days', $('[name=\'expiryValue\']').val());
    }
    $('[name=\'expiryValue\']').val('');
  }

  if ($(this).val() == 'forever') {
    $('.expiry-value-js').addClass('hidden');
  } else {
    $('.expiry-value-js').removeClass('hidden');
    var $esBlock = $('.expiry-value-js > .controls > .help-block');
    $esBlock.text($esBlock.data($(this).val()));
  }
  toggleExpiryValue($(this).val());
});

function initEditor() {
  CKEDITOR.replace('about', {
    allowedContent: true,
    toolbar: 'Detail',
    fileSingleSizeLimit: app.fileSingleSizeLimit,
    filebrowserImageUploadUrl: $('#about').data('imageUploadUrl'),
    filebrowserFlashUploadUrl: $('#about').data('flashUploadUrl')
  });

  $('[name="categoryId"]').select2({
    treeview: true,
    dropdownAutoWidth: true,
    treeviewInitState: 'collapsed',
    placeholderOption: 'first'
  });
}

function initValidator() {
  return $('#classroom-set-form').validate({
    rules: {
      title: {
        required: true,
      }
    },
  });
}

function toggleExpiryValue(expiryMode) {
  const $expriySetting = $('.expiry-value-js');
  if (!$('[name=\'expiryValue\']').val()) {
    $('[name=\'expiryValue\']').val($('[name=\'expiryValue\']').data(expiryMode));
  }
  elementRemoveRules($('[name=\'expiryValue\']'));
  $expriySetting.removeClass('has-error').find('.jq-validate-error').remove();
  $expriySetting.find('input').removeClass('form-control-error');
  switch (expiryMode) {
  case 'days':
    $('[name="expiryValue"]').datetimepicker('remove');
    $('.expiry-value-js .controls > span').removeClass('hidden');
    elementAddRules($('[name="expiryValue"]'),getExpiryModeDaysRules());
    break;
  case 'date':
    if($('#classroom_expiryValue').attr('readonly') !== undefined){
      return false;
    }
    $('.expiry-value-js .controls > span').addClass('hidden');
    $('#classroom_expiryValue').datetimepicker({
      language: document.documentElement.lang,
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 'month',
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    }).on('hide', () => {
      validator.form();
    });
    $('#classroom_expiryValue').datetimepicker('setStartDate', new Date);
    elementAddRules($('[name="expiryValue"]'),getExpiryModeDateRules());
    break;
  default:
    break;
  }
}

function getExpiryModeDaysRules() {
  return {
    required: true,
    digits:true,
    min: 1,
    max: 10000,
    messages: {
      required: Translator.trans('classroom.manage.expiry_mode_days_error_hint'),
    }
  };
}

function getExpiryModeDateRules() {
  return {
    required: true,
    date: true,
    after_now_date: true,
    messages: {
      required: Translator.trans('classroom.manage.expiry_mode_date_error_hint'),
    }
  };
}

function elementAddRules($element, options) {
  $element.rules('add', options);
}

function elementRemoveRules($element) {
  $element.rules('remove');
}