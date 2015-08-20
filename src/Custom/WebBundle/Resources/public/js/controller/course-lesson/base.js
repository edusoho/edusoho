define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('a.course-before-open').click(function(){
            Notify.danger("未到开课时间，等待开课。");
        })
    };

});