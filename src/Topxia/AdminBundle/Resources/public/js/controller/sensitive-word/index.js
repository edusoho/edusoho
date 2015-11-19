define(function(require, exports, module) {

	exports.run = function() {
		$('input:radio[name="enabled"]').click(function(){
			var value = $('input:radio[name="enabled"]:checked').val();
			if(value == 1) {
				$("#sensitiveWordRules").show();
			} else {
				$("#sensitiveWordRules").hide();
			}
		})
	}

});