define(function(require, exports, module) {

    exports.run = function() {

        var $panel = $('#file-manage-panel');
	    require('../../util/batch-select')($panel);
	    require('../../util/batch-delete')($panel);

    };

});