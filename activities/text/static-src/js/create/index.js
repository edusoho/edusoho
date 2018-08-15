// import Text from './text';
// new Text();

window.ltc.loadCss();
let load = window.ltc.load('jquery', 'validate');
load.then(function(){
  window.ltc.messenger.on('next', (msg) => {
    window.ltc.messenger.sendToParent('nextReturn', {success:true,data:[{'name': 'finishDetail','value': '123456'}]});
  });
});