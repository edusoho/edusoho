define(function(require, exports, module) {
    var ThreadShowWidget = require('../thread/thread-show.js');

	exports.run = function() {
        
        var threadShowWidget = new ThreadShowWidget({
            element: '#open-course-comment',
        });

		$('.tab-header').on('click', function() {
			var $this = $(this);
			var index = $this.index();
  			$this.addClass('active').siblings().removeClass('active');
  			$('#content').find('ul').eq(index).show().siblings().hide();
		});

		$("#favorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                $btn.hide();
                if (data['result']) {
                    $("#unfavorite-btn").show();
                    // $("#unfavorite-btn").find('.gray-darker').html(parseInt(data['number']));
                }
            });
        });

        $("#unfavorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                $btn.hide();
                if (data['result']) {
                    $("#favorite-btn").show();
                    // $("#favorite-btn").find('.gray-darker').html(parseInt(data['number']));
                }
            });
        });

         $("#zan-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                $btn.hide();
                if (data['result']) {
                    $("#unzan-btn").show();
                    $("#unzan-btn").find('.likeNum').html(parseInt(data['number']));
                }
            });
        });

        $("#unzan-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                $btn.hide();
                if (data['result']) {
                    $("#zan-btn").show();
                    $("#zan-btn").find('.likeNum').html(parseInt(data['number']));
                }
            });
        });

        $('.open-course-qrcode').on('hover',function(){
            var qrcodeUrl = $(this).data('url');console.log('url='+qrcodeUrl);
            $.post(qrcodeUrl,function(response){
                $(this).find('.qrcode-content img').attr('src',response.img);
            })
        })
        
	};
});