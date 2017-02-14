define(function(require, exports, module) {
  var Notify = require('common/bootstrap-notify');

  exports.run = function (){
    $('a[role=filter-change]').click(function(event){
      window.location.href = $(this).data('url');
    });

    $('.receive-modal').click();

    $('body').on('click', '.check-account', function() {
      var url = $(this).data('url');
      window.location.href(url);
    });
  };
});