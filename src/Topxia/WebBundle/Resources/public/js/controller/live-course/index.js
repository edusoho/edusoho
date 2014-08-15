define(function(require, exports, module) {

	require('jquery.cycle2');

	exports.run = function() {
		require('../course/timeleft').run();
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