define(function(require, exports, module) {

    exports.run = function() {

        var $container = $('#course-lesson-table-container');
	    var $table = $("#course-lesson-table");
	    require('../../util/batch-select')($container);
	    require('../../util/batch-delete')($container);

    };

});