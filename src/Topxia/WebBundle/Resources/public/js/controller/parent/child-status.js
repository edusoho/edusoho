define(function(require, exports, module) {

	exports.run = function() {
		$("#moreStatusesBtn").click(function(){
			var self=$(this);

			$.get(self.data('url'),function(html){
				console.log(html);
			});
		});
	}
});