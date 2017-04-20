define(function(require, exports, module) {

	exports.run = function() {
		$('a[data-role="announcement-modal"]').click(function(){

      var $modal = $("#modal");
			$modal.html("").load($(this).data('url'));
		})
	}
});