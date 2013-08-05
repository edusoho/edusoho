define(function(require, exports, module) {
	var $ = require('jquery');

	exports.bootstrap = function(options) {
		$(function(options) {
			$("#collect-form").submit(function(){
				$form = $(this);
				$btn = $(this).find('input[type=submit]');
				$btn.attr('disabled', 'disabled');

				var type = $(this).data('type');

				var url = $(this).find('input[name=url]').val();
				$.post('', {url:url, 'type':type}, function(response){
					if (response.error) {
						alert(response.error);
						$btn.removeAttr('disabled');
						return ;
					}
					$form.append('<p><a href="' + response.url + '">查看结果</a></p>' );

				}, 'json');

				return false;
			});
		});
	};

});
