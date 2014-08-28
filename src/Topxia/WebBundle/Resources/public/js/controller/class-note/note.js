define(function(require, exports, module) {
        
  exports.run = function() {
    var $table = $("#note-list");
    require('../../util/short-long-text')($table);
  };
    
});