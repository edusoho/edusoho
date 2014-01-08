define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require("jquery.bootstrap-datetimepicker");
    require("$");
    exports.run = function() {
        var $container = $('#quiz-table-container');
        require('../../util/batch-select')($container);
        require('../../util/batch-delete')($container);
        require('../../util/item-delete')($container);

        $('.test-paper-reset').on('click','',function(){
        	if (!confirm('重置会清空原先的题目,确定要继续吗？')) {
        	    return ;
        	}
        });

    };


});