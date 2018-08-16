
window.ltc.loadCss();
let load = window.ltc.load('jquery', 'validate');
load.then(function(){
  window.ltc.messenger.on('getFinishCondition', (msg) => {
    window.ltc.messenger.sendToParent('returnFinishCondition', {valid:true,data:$('#step3-form').serializeObject()});
  });
});