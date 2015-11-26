define(function(require, exports, module) {
	var Notify = require('common/bootstrap-notify');

	exports.run = function() {
		$("span[role='replay-name-span']").click(function(){
			var replayId = $(this).data("replayId");
			$(this).hide();
			$("#replay-name-input-"+replayId).show();
			//$("#replay-name-input-"+replayId).focus();
		})

		$("input[role='replay-name-input']").blur(function(){
			var self = $(this);
			$(this).hide();
			var replayId = $(this).data("replayId");
			$("#replay-name-span-"+replayId).show();
			$.post(self.data('url'), {id:replayId}, function(data){
				if(data == 'true'){
					$("#replay-name-span-"+replayId).text(self.value);
				}
			})
		})
	}
});