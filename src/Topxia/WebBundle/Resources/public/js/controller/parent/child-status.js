define(function(require, exports, module) {

	exports.run = function() {
		$("#moreStatusesBtn").click(function(){
			var self=$(this);
			$("#count").val(parseInt($("#count").val())+1);
			$.get(self.data('url')+"&count="+$("#count").val(),function(html){
				$('.timeline').html($('.timeline').html()+html);
			});
			if(parseInt($("#count").val())*30+30>parseInt($("#statusCount").val())){
				self.hide();
			}
		});
	}
});