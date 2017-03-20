define(function(require, exports, module){
  var Cookie = require('cookie');

  var PCSwitcher = $('.js-switch-pc');
  var MobileSwitcher = $('.js-switch-mobile');
  if(PCSwitcher.length){
    PCSwitcher.on('click', function(){
      Cookie.set('PCVersion', 1);
      window.location.reload();
    })
  }
  if(MobileSwitcher.length){
    MobileSwitcher.on('click', function(){
      Cookie.remove('PCVersion');
      window.location.reload();
    })
  }

  $('.js-back').click(function(){
    if(history.length !== 1){
      history.go(-1);
    } else {
      location.href = '/';
    }
  })
})