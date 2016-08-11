define(function(require, exports, module) {
    require('../../util/qrcode').run();
    require('jquery.countdown');
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

        var remainTime = parseInt($('#discount-endtime-countdown').data('remaintime'));
        if (remainTime >=0) {
            var endtime = new Date(new Date().valueOf() + remainTime * 1000);
            $('#discount-endtime-countdown').countdown(endtime, function(event) {
               var $this = $(this).html(event.strftime('剩余 '
                 + '<span>%D</span> 天 '
                 + '<span>%H</span> 时 '
                 + '<span>%M</span> 分 '
                 + '<span>%S</span> 秒'));
             }).on('finish.countdown', function() {
                $(this).html('活动时间到，正在刷新网页，请稍等...');
                setTimeout(function() {
                    $.post(app.crontab, function(){
                        window.location.reload();
                    });
                }, 2000);
             });
        }

        $(".cancel-refund").on('click', function() {
            if (!confirm('真的要取消退款吗？')) {
                return false;
            }

            $.post($(this).data('url'), function() {
                window.location.reload();
            });
        });
    };

});