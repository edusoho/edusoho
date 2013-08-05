define(function(require, exports, module) {

	var $ = require('jquery');
	require('jquery.ui.sortable')($);

	exports.bootstrap = function(options) {
		$(function(options) {
			$('.generate-btn').click(function() {

				var $form = $(this).parents('tr').next().find('form');

				var iids = decodeURIComponent($form.serialize());

				if (!iids) {
					alert('请选择一些课时');
					return false;
				}

				var url = $(this).attr('href') + '&' + iids;

				window.location = url;

				return false;
			});

			$('.archive-btn').click(function(){
				$.post($(this).attr('href'), function(){
					window.location.reload();
				});
				return false;
			});

			$('.select-all').click(function() {
				var $checkboxs = $(this).parents('td').next().find('[type=checkbox]').not(':disabled');
				if ($(this).attr('checked')) {
					$checkboxs.attr('checked', 'checked');
				} else {
					$checkboxs.removeAttr('checked');
				}
			});
		});
	};

});
