define(function(require, exports, module) {
    require('../../util/qrcode').run();
    exports.run = function() {

        $('.course-exit-btn').on('click', function(){
        	var $btn = $(this);

        	if (!confirm('您真的要退出学习吗？')) {
        		return false;
        	}

        	$.post($btn.data('url'), function(){
        		window.location.href = $btn.data('goto');
        	});
        });

        $('.js-exit-course').on('click', function(){
            var self = $(this);
            $.post($(this).data('url'), function(){
                window.location.href = self.data('go');
            });
        });

        $("#favorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function() {
                $btn.hide();
                $("#unfavorite-btn").show();
            });
        });

        $("#unfavorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function() {
                $btn.hide();
                $("#favorite-btn").show();
            });
        });


    };

});