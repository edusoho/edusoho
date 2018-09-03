let $selectFinish = $('#finish-condition');
if ($selectFinish.length) {
  $('#finish-condition').on('change',function() {
    // switch($(this).)
    // {
    // case 1:
    //   break;
    // case 2:
    //   break;
    // default:
    // }
  });
}

$('#text').on('change', function() {
  $('#finish-data').val($(this).val());
  alert($('#finish-data').val());

});

window.ltc.on('getCondition', function(msg){
  window.ltc.emit('returnCondition', {valid: true ,data: {finishType: $('#finish-type').val(), finishData: $('#finish-data').val()} });
});