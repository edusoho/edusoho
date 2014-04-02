define(function(require, exports, module) {

	require('jquery.cycle2');

    exports.run = function() {

        $('#deadlineAlert').on('click', function() {
            document.cookie = " deadlineAlert= " + escape("closed");
        });

        $('.homepage-feature').cycle({
	        fx:"scrollHorz",
	        slides: "> a, > img",
	        log: "false",
	        pauseOnHover: "true"
    	});
        
    };

});