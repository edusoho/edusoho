
window.ltc.loadCss();
let load = window.ltc.load('jquery', 'validate');
load.then(function(){
  window.ltc.messenger.on('next', (msg) => {
    window.ltc.messenger.sendToParent('nextReturn', {success:true,data:[{'name': 'title','value': '123456'}]});
  });
});