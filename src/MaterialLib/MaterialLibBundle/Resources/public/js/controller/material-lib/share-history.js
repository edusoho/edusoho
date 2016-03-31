define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');
	    require('jquery.select2-css');
	    require('jquery.select2');
	    
	exports.run = function() {
		var $list = $("#share-history-table");

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
			$('.share-show-users-thead').show();
			$('.share-show-users-tbody').show();
			$('.share-history-detail-thead').hide();
			$('.share-history-detail-tbody').hide();
		});

		$(".js-share-history-detail").on('click', function(){
			$('.share-history-detail-thead').show();
			$('.share-history-detail-tbody').show();
			$('.share-show-users-thead').hide();
			$('.share-show-users-tbody').hide();
		});

		$(".show-share-history").on('click', function(){
			$.post($(this).data('url'),function(html){
				$(".share-body").html();
				$(".share-body").html(html);
			});
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