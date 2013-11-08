define(function(require, exports, module) {

    exports.run = function() {
        require('../course-manage/header').run();

        var $panel = $('#file-manage-panel');
	    require('../../util/batch-select')($panel);
	    require('../../util/batch-delete')($panel);

    };

});