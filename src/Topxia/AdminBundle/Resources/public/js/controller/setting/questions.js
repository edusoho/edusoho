define(function(require, exports, module) {
	
	$('[name="answers_show_enabled"]').on("click",function(){
		var answersShowEnabled = $(this).val();
		if (answersShowEnabled == 1) {
			$('#answers-show-all').show();
		} else {
			$('#answers-show-all').hide();
		}
	});
	
});
