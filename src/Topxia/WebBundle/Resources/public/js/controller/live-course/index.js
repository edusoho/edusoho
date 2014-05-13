define(function(require, exports, module) {

	exports.run = function() {
        $('.live-rating-course').find('.media-body').hover(function() {
        	$( this ).find( ".rating" ).show();
        }, function() {
        	$( this ).find( ".rating" ).hide();
        });;
	};

});