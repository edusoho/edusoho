define(function(require, exports, module) {
        
  exports.run = function() {

  	$('tbody').on('click', 'tr.folder', function() {
        var url = $(this).data('url');
        console.log(url);

    });

  };
    
});