define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        var title =$('#course_old_title').data('title');
        $('#course_old_title').attr('value', title);

    };
})