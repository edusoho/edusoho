define(function(require, exports, module) {
        
  exports.run = function() {

    var $container = $('#offsale-table-container');
    var $table = $("#offsale-table");
    require('../../util/short-long-text')($table);
    require('../../util/batch-select')($container);
    require('../../util/batch-delete')($container);
    require('../../util/item-delete')($container);

  };
    
});