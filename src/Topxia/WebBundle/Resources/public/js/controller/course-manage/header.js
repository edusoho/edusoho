define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    exports.run = function() {

        $('.course-publish-btn').click(function() {
            if (!confirm('您真的要发布该课程吗？')) {
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