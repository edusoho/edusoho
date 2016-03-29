define(function(require, exports, module) {
	exports.run = function() {
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

         $("#zan-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function() {
                $btn.hide();
                $("#unzan-btn").show();
            });
        });

        $("#unzan-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function() {
                $btn.hide();
                $("#zan-btn").show();
            });
        });

        $("#alert-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function() {
                $btn.hide();
                $("#alerted-btn").show();
            });
        });

        $("#alerted-btn").on('click', function() {
            var $btn = $(this);
            $.post($btn.data('url'), function() {
                $btn.hide();
                $("#alert-btn").show();
            });
        });
	};
});