define(function(require, exports, module) {

    exports.run = function() {

        var $container = $('#course-material-table-container');
	    var $table = $("#course-material-table");
	    require('../../util/batch-select')($container);
	    require('../../util/batch-delete')($container);

    };

});