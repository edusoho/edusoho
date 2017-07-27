define(function(require, exports, module){

  exports.run = function(){

    $('.js-restore-btn').on('click', function () {
      $.ajax({
        url: $(this).data('url'),
        type: 'POST',
        success: function () {
          window.location.reload();
        }
      });
    });


  };
});

