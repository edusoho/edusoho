define(function(require, exports, module) {
    var ThreadShowWidget = require('../thread/thread-show.js');
    var Notify = require('common/bootstrap-notify');

	exports.run = function() {
        
        if (!$('#open-course-comment').find('[type=submit]').hasClass('disabled')) {
            var threadShowWidget = new ThreadShowWidget({
                element: '#open-course-comment',
            });
        }

		$('.tab-header').on('click', function() {
			var $this = $(this);
			var index = $this.index();
  			$this.addClass('active').siblings().removeClass('active');
  			$('#content').find('ul').eq(index).show().siblings().hide();
		});

		$("#favorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                console.log(data);
                if (data['result']) {
                    $btn.hide();
                    $("#unfavorite-btn").show();
                    // $("#unfavorite-btn").find('.gray-darker').html(parseInt(data['number']));
                } else {
                    Notify.danger(data['message']);
                }
            });
        });

        $("#unfavorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                console.log(data);
                if (data['result']) {
                    $btn.hide();
                    $("#favorite-btn").show();
                    // $("#favorite-btn").find('.gray-darker').html(parseInt(data['number']));
                } else {
                    Notify.danger(data['message']);
                }
            });
        });

         $("#zan-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                if (data['result']) {
                    $btn.hide();
                    $("#unzan-btn").show();
                    $("#unzan-btn").find('.likeNum').html(parseInt(data['number']));
                }
            });
        });

        $("#unzan-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                if (data['result']) {
                    $btn.hide();
                    $("#zan-btn").show();
                    $("#zan-btn").find('.likeNum').html(parseInt(data['number']));
                }
            });
        });

        $('.course-operation').on('mouseover','.open-course-qrcode',function(){
            $self = $(this);
            var qrcodeUrl = $(this).data('url');

            $.post(qrcodeUrl,function(response){
                $self.find('.qrcode-content img').attr('src',response.img);
            })
        })
        
	};
});