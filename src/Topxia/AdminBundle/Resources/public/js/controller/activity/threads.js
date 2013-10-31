define(function(require, exports, module) {
        
    exports.run = function() {
        var $element = $('#thread-table-container');
        require('../../util/short-long-text')($element);
        require('../../util/batch-select')($element);
        require('../../util/batch-delete')($element);
        require('../../util/item-delete')($element);
    };

  });

