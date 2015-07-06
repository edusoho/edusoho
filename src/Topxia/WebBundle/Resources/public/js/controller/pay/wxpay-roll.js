define(function(require, exports, module){

	exports.run = function() {
		var $img = $('.img-js');
		setInterval(order_query,2000);
		setInterval(wxpay_roll,2000);

		function wxpay_roll () {
			$.get($img.data('url'), function(response) {
				if (response) {
					window.location.href = $img.data('goto');
				};
			});	
		}

		function order_query () {
			$.get($img.data('check'), function(response) {
			if (response) {
					window.location.href = $img.data('goto');
				};
			});
		}

	};
});