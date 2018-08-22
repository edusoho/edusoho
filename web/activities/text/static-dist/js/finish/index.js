
window.ltc.loadCss();
var load = window.ltc.load('jquery', 'validate');
load.then(function(){
  window.ltc.messenger.on('getFinishCondition', function(msg){
    window.ltc.messenger.sendToParent('returnFinishCondition', {valid:true,data:$('#step3-form').serializeObject()});
  });
});