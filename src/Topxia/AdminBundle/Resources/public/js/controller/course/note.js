define(function(require, exports, module) {
        
  exports.run = function() {

    var $container = $('#note-table-container');
    var $table = $("#note-table");
    require('../../util/short-long-text')($table);
    require('../../util/batch-select')($container);
    require('../../util/batch-delete')($container);
    require('../../util/item-delete')($container);

  };
    
});