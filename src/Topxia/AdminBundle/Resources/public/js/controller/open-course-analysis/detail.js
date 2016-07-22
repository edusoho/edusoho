define(function(require, exports, module) {
	
	exports.run = function() {

		$(".modal").off('click.modal-pagination');
		$(".modal").on('click', '.pagination a', function(e) {
			e.preventDefault();
			var urls = $(this).attr('href').split('?');
			url = [$("#pageinator-url").val(), urls[1]].join('?');
			$.get(url, function(html) {
				$(".referer-log-list").html(html);
			})
		});

		$('[data-toggle="popover"]').popover();
	}
});