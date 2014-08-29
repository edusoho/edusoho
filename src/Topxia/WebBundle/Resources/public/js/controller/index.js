define(function(require, exports, module) {
    var Share = require('../util/share.js');
    require('jquery.cycle2');

    exports.run = function() {

    	$('.homepage-feature').cycle({
	        fx:"scrollHorz",
	        slides: "> a, > img",
	        log: "false",
	        pauseOnHover: "true",
    	});

    Share.create({
        selector: '.share',
        icons: 'itemsAll',
        display: '',
    });
    };

});