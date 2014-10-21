define(function(require, exports, module) {

	exports.run = function() {
        $('#live-course_categoryId').change(function(){
            var url = $('#live-course_categoryId').find('option:selected').data('url');
            window.location.href= url;
        });

        $('.live-rating-course').find('.media-body').hover(function() {
        	$( this ).find( ".rating" ).show();
        }, function() {
        	$( this ).find( ".rating" ).hide();
        });
	};

});