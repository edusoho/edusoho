define(function(require, exports, module) {

    var SocialShare = require('common/social-share');

    exports.run = function() {
        var social = new SocialShare(app.config.api);

        $('.js-social-shares').on('click', '[data-share]', function() {
            var $this = $(this);
            social.share($this.data('share'), $($this.data('params')).data());
        });

        $('.course-exit-btn').on('click', function(){
        	var $btn = $(this);

        	if (!confirm('您真的要退出学习吗？')) {
        		return false;
        	}

        	$.post($btn.data('url'), function(){
        		window.location.href = $btn.data('goto');
        	});
        });

        $('#next-learn-btn').tooltip({placement: 'top'});
        $('#question-sign').tooltip({placement: 'right'});
    };

});