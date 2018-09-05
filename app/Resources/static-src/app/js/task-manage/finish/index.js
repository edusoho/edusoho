let $selectFinish = $('#finish-type');
if ($selectFinish.length) {
  $selectFinish.on('change',function() {
    $('#conditions').children().hide();
    let val = $(this).val();
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

$('#watchTime').on('change', function() {
  $('#finish-data').val($(this).val());
});

window.ltc.on('getCondition', function(msg){
  window.ltc.emit('returnCondition', {valid: true ,data: {finishType: $('#finish-type').val(), finishData: $('#finish-data').val()} });
});