define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $('.course-publish-btn').click(function() {

            var studentNum = $('input[name=maxStudentNum]').val();

            if (!confirm(Translator.trans('您真的要发布该课程吗？'))) {
                return ;
            }

            $.get($('.course-num-check-btn').data('url'), function(response) {
                if (!response['success']) {
                    Notify.danger(response['message']);
                    return ;
                }
                $.post($('.course-publish-btn').data('url'), function(response) {
                    if (!response['result']) {
                        Notify.danger(response['message']);
                    } else {
                        window.location.reload();
                    }
                });
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