define(function(require, exports, module) {
    exports.run = function() {
        var $element = $('#thread-table-container');
        require('../../../../topxiaadmin/js/util/short-long-text')($element);
        require('../../../../topxiaadmin/js/util/batch-select')($element);
        require('../../../../topxiaadmin/js/util/batch-delete')($element);
        require('../../../../topxiaadmin/js/util/item-delete')($element);
    };

  });

