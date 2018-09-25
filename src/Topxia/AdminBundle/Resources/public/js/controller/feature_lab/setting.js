define(function(require, exports, module) {


	exports.run = function() {
		$('input[name=face_identify]').click(function() {
			var data={face_identify: 0};
			if ($(this).is(':checked')) {
				data.face_identify = $(this).val();
			}
			$.post(document.location.href, data, function(data){
				
			})
		})
	}
})