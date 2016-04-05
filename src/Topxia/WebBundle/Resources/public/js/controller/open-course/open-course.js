define(function(require, exports, module) {
    var ThreadShowWidget = require('../thread/thread-show.js');

	exports.run = function() {
        
        var threadShowWidget = new ThreadShowWidget({
            element: '#open-course-comment',
        });

		$('.tab-header').on('click', function() {
			var $this = $(this);
			var $index = $this.index();
  			var $content = $this.parent().find('#content');
  			$this.addClass('active').siblings().removeClass('active');
  			$content.find('ul:eq($index)').show().siblings().hide();
			console.log($content.find('ul:eq(1)'));
		});

						
			// $('#open-tab li').mouseover(function(){
			// 	var $this = $(this);
			// 	var $li = $('#open-tab li');
			// 	var $ul = $('#content ul');
			// 	var $t = $this.index();
			// 	$li.removeClass();
			// 	$this.addClass('current');
			// 	$ul.css('display','none');
			// 	$ul.eq($t).css('display','block');
			// })
		$("#favorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                $btn.hide();

                if (data['result']) {
                    $("#unfavorite-btn").show();
                    $("#unfavorite-btn").find('.gray-darker').html(parseInt(data['number']));
                }
                
            });
        });

        $("#unfavorite-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                $btn.hide();
                if (data['result']) {
                    $("#favorite-btn").show();
                    $("#favorite-btn").find('.gray-darker').html(parseInt(data['number']));
                }
            });
        });

         $("#zan-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                $btn.hide();
                if (data['result']) {
                    $("#unzan-btn").show();
                    $("#unzan-btn").find('.gray-darker').html(parseInt(data['number']));
                }
            });
        });

        $("#unzan-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function(data) {
                $btn.hide();
                if (data['result']) {
                    $("#zan-btn").show();
                    $("#zan-btn").find('.gray-darker').html(parseInt(data['number']));
                }
            });
        });

        
	};
});