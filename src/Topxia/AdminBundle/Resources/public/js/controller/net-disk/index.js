define(function(require, exports, module) {
        
  exports.run = function() {

  	$('tbody').on('click', 'tr.folder', function() {
        window.location.href = $(this).data('url')
    });

  };
    
});