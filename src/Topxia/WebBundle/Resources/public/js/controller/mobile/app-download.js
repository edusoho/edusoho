define(function(require, exports, module) {
  $closeBtn = $('.app-download .js-close-btn');
  $closeBtn.on('click', function(){
    $closeBtn.parent().slideUp(300);
    $('body').removeClass('has-app');
  })
});
