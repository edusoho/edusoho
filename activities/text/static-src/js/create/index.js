// import Text from './text';
// new Text();

window.ltc.loadCss();
let load = window.ltc.load('jquery', 'validate');
load.then(function(){
  alert(123);
  console.log($);
  console.log($.validate);
});