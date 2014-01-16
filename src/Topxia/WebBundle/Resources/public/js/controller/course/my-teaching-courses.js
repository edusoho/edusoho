define(function(require, exports, module) {
	require('jquery.form');

	exports.run = function() {
		$('#create-course').on('click', function() {
			if (!$('.avatar').text()) {
				console.log($('#avatar').text());
				$('.set-avatar').show();
					if (!$('.title').text()) {
						$('.set-title').show();}
				}else if (!$('.title').text()) {
					$('.set-title').show(); 
				}else {
				window.location.href=$(this).data('url');
			}
        });
	};
});