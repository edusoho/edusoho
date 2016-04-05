define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	    require('jquery.select2-css');
	    require('jquery.select2');
	    
	exports.run = function() {
		var $list = $("#share-history-table");

		$.get($(".js-share-users").data('url'),function(html){
			$('.share-show-users-tbody').html();
			$('.share-show-users-tbody').html(html);
			pagEvent();
		});

		$list.on('click', '.cancel-share-btn', function(e) {
			var $btn = $(e.currentTarget);
			$.post($(this).data('url'), {targetUserId: $(this).attr('targetUserId')}, function(response) {
				$btn.parents('.share-history-record').remove();
				Notify.success('已取消分享！');
				//window.location.reload();
			}, 'json');
		});
		
		$("#modal").modal({
			backdrop : 'static',
			keyboard : false,
			show : false
		});

		$(".js-share-users").on('click', function(){
			$.get($(this).data('url'),function(html){

				$('.share-show-users-tbody').html();
				$('.share-show-users-tbody').html(html);
				pagEvent();
			})
			$(this).parent().addClass('active');
			$(".js-share-history-detail").parent().removeClass('active');
		});

		var pagEvent = function(){
			$(".pagination li").on('click', function(){
					var self = $(this);
					var page = self.data('page');

					$('.js-page').val(self.data('page'));

					$.get($(".pagination").data('url'),{'page':page},function(html){
						
						$('.share-show-users-tbody').html(html);
						pagEvent();
					});

				});
		}
		$(".js-share-history-detail").on('click', function(){
			$.get($(this).data('url'),function(html){
				$('.share-show-users-tbody').html();
				$('.share-show-users-tbody').html(html);
				pagEvent();
			});
			$(this).parent().addClass('active');
			$(".js-share-users").parent().removeClass('active');
		});


		$("#share").on('click',function(){
		  $("#show-share-input").show();
		  $("#show-share-input").animate({ left:'0px' },1000 );
		}); 

		$("#close-share-input").on('click',function(){
			$("#show-share-input").animate({ left:'-250px' },1000,function(){
				$("#show-share-input").hide();
			});
		});

		$("#close-share-input").on('click',function(){
			$("#show-share-input").animate({ left:'-250px' },1000,function(){
				$("#show-share-input").hide();
			});
		});

		$("#modal").on("hidden.bs.modal", function() {
			window.location.reload();
		});
	}

});