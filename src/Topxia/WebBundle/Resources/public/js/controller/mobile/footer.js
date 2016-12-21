define(function(require, exports, module){
  var Cookie = require('cookie');

  var toggleBtn = $('.js-change-pc');
  var PCVersion = Cookie.get('PCVersion');
  parseInt(PCVersion) ? toggleBtn.html('使用触屏版') : toggleBtn.html('使用电脑版') 

  exports.run = function(){
    $('.js-change-pc').on('click', function(){
      var PCVersion = Cookie.get('PCVersion');
      if(!parseInt(PCVersion)){
        Cookie.set('PCVersion', 1);
      }else{
        Cookie.remove('PCVersion');
      }
      window.location.reload();
    })
  }
})