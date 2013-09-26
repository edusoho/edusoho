define(function(require, exports, module) {

    var SocialShare = require('common/social-share');

    exports.run = function() {
        var social = new SocialShare(app.config.api);

        $('.js-social-shares').on('click', '[data-share]', function() {
            var $this = $(this);
            social.share($this.data('share'), $($this.data('params')).data());
        });

        $('.course-exit-btn').click(function() {
            if (!confirm('您真的要退出该课程的学习吗？')) {
                return ;
            }

            $.post($(this).data('url'), function() {
                window.location.reload();
            });
        });

        $('#next-learn-btn').tooltip({placement: 'top'});
    };

});