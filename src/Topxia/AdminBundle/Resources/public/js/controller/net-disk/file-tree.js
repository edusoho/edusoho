define(function(require, exports, module) {
        
  exports.run = function() {

  	var $container = $('#course-files-table-container');
    var $table = $("#course-files-table");
    require('../../util/batch-select')($container);
    require('../../util/batch-delete')($container);
     
  };
    
});