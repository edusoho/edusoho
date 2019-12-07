let $selectFinish = $('#finish-type');
if ($selectFinish.length) {
  $selectFinish.on('change',function() {
    $('#conditions').children().hide();
    let val = $(this).val();
    if ('time' == val) {
      $('#watchTime').rules('add', {
        required: true,
        positive_integer: true,
        messages: {
          required: Translator.trans('activity.video_manage.length_required_error_hint')
        }
      });
    } else {
      $('#watchTime').rules('remove');
    }
    
    switch(val)
    {
    case 'time':
      $('#conditions-time').show();
      if (!$('#watchTime').val()) {
        let $options = $('#finish-type option:selected');
        $('#watchTime').val($options.data('value'));
        $('#finish-data').val($options.data('value'));
      }
      break;
    case 'end':
      break;
    default:
      $selectFinish.trigger('selectChange', val);
    }
  });
}


let validate = $('#step3-form').validate({
  groups: {
    nameGroup: 'minute second'
  },
  rules: {
    watchTime: {
      positive_integer: true,
    },
  }
});

if ($('#conditions-time').css('display') != 'none') {
  $('#watchTime').rules('add', {
    required: true,
    positive_integer: true,
    messages: {
      required: Translator.trans('activity.video_manage.length_required_error_hint')
    }
  });
}

$('#watchTime').on('change', function() {
  $('#finish-data').val($(this).val());
});

window.ltc.on('getCondition', function(msg){
  if ($('#finish-type-select').length > 0) {
    window.ltc.emit('returnCondition', {valid: validate.form() ,data: {finishType: $('#finish-type-select:checked').val()} });
  } else {
    window.ltc.emit('returnCondition', {valid: validate.form() ,data: {finishType: $('#finish-type').val(), finishData: $('#finish-data').val()} });
  }
});