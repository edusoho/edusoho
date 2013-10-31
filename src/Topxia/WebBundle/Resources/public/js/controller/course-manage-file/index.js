define(function(require, exports, module) {

    exports.run = function() {

        var $container = $('#course-ware-table-container');
	    var $table = $("#course-ware-table");
	    require('../../util/batch-select')($container);
	    require('../../util/batch-delete')($container);
	    require('../../util/batch-rename')($container);

    };

});