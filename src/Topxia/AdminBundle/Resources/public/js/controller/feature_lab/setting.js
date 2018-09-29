define(function(require, exports, module) {


	exports.run = function() {
		$('input[name=enabled]').click(function() {
			var data={enabled: 0};
			if ($(this).is(':checked')) {
				data.enabled = $(this).val();
			}
			$.post(document.location.href, data, function(data){
				document.location.reload();
			})
		})
	}
})