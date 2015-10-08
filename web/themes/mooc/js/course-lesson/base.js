define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {
        $('a.course-before-open').click(function(){
            Notify.danger("未到开课时间，等待开课。");
        })

        $('a.course-ended').click(function(){
            Notify.danger("本期课程已结束，请等待下次开课。");
        })
    };

});