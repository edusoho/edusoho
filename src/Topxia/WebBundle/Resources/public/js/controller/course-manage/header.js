define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $('.course-publish-btn').click(function() {

            var studentNum = $('input[name=maxStudentNum]').val();

            if (studentNum <= 0) {
                Notify.danger(Translator.trans('请先设置课程人数，再发布课程，否则用户无法加入/购买此课程。'));
                return;
            }

            if (!confirm(Translator.trans('您真的要发布该课程吗？'))) {
                return ;
            }

            $.post($(this).data('url'), function(response) {
                if (!response['result']) {
                    Notify.danger(response['message']);
                } else {
                    window.location.reload();
                }
            });

        });
        $('.js-exit-course').on('click', function(){
            var self = $(this);
            $.post($(this).data('url'), function(){
                window.location.href = self.data('go');
            });
        });
    };

});