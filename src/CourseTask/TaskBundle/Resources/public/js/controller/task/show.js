define(function (require, exports, module) {
    exports.run = function () {
    	var iframe = $('#task-detail');
    	$.post(iframe.data('taskStartUrl'), function(data){});
    }
});