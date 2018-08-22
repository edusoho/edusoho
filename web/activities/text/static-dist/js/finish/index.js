
var load = window.ltc.load('bootstrap.css', 'jquery', 'validate');
load.then(function(){
  var context = window.ltc.getContext();

  window.ltc.messenger.on('getFinishCondition', function(msg){
    window.ltc.messenger.sendToParent('returnFinishCondition', {valid:true,data:$('#step3-form').serializeObject()});
  });

  if (context.activityId) {
    window.ltc.api({
      name: 'getActivity',
      pathParams: {
        id: context.activityId
      }
    }, function(result) {
      $('#finishDetail').val(result['finishDetail']);
    });
  } else {
    $('#finishDetail').val(1);
  }
});