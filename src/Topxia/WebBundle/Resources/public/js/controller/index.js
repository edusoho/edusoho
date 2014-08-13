define(function(require, exports, module) {

    require('jquery.cycle2');

    exports.run = function() {
    	require('./course/timeleft').run();

    	$('.homepage-feature').cycle({
	        fx:"scrollHorz",
	        slides: "> a, > img",
	        log: "false",
	        pauseOnHover: "true",
    	});

    };

});