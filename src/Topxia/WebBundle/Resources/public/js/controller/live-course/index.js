define(function(require, exports, module) {

	require('jquery.cycle2');

	exports.run = function() {
        $('.homepage-feature').cycle({
	        fx:"scrollHorz",
	        slides: "> a, > img",
	        log: "false",
	        pauseOnHover: "true",
    	});

        $('.live-rating-course').find('.media-body').hover(function() {
        	$( this ).find( ".rating" ).show();
        }, function() {
        	$( this ).find( ".rating" ).hide();
        });

	};

});