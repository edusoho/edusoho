let $selectFinish = $('#finish-type');
if ($selectFinish.length) {
  $selectFinish.on('change',function() {
    $('#conditions').children().hide();
    let val = $(this).val();
    'time' == val ?  $('#watchTime').rules('add', 'positive_integer') : $('#watchTime').rules('remove');
    
    switch(val)
    {
    case 'time':
      $('#conditions-time').show();
      if (!$('#watchTime').val()) {
        let $options = $('#finish-type option:selected');
        $('#watchTime').val($options.data('value'));
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
    watchTime: 'positive_integer',
  }
});

if ($('#conditions-time').css('display') != 'none') {
  $('#watchTime').rules('add', 'positive_integer');
}

$('#watchTime').on('change', function() {
  $('#finish-data').val($(this).val());
});

window.ltc.on('getCondition', function(msg){
  window.ltc.emit('returnCondition', {valid: validate.form() ,data: {finishType: $('#finish-type').val(), finishData: $('#finish-data').val()} });
});