$(function() {
	$('div[data-role="tab"]').click(function(){
		var isStudent = $(this).attr('class') == 'prev' ? true : false;

		if (isStudent) {
			$('div.features ul:eq(0)').show();
			$('div.features ul:eq(1)').hide();
		} else {
			$('div.features ul:eq(1)').show();
			$('div.features ul:eq(0)').hide();
		}
	});
});