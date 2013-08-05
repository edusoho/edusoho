define(function(require, exports, module) {

	require('jquery.lazyload')($);

	var onReady = function() {
		$('img.lazy-load').lazyload();

		var $form = $('form.img-monitor-form');
		$form.find('select').change(function(){
			$form.submit();
		});

		$('a.delete-btn').click(function(){
			var $deleteBtn = $(this);
			if (!confirm('真的要删除该图片吗？')) return;
			$.post($deleteBtn.data('url'), function(response){
				console.log(response);
				if (response.status == 'error') {
					alert('删除错误：' + response.error.message);
				} else {
					$deleteBtn.parents('li').remove();
				}
			}, 'json');
			return ;
		});
	};

	exports.bootstrap = function() {
		onReady();
	};

});