define(function(require, exports, module) {

	var VideoJS = require('video-js');

    exports.run = function() {

		if ($("#lesson-preview-video-player").length > 0) {
			var player = VideoJS("lesson-preview-video-player");
	    	player.play();

	    	$('#modal').one('hidden.bs.modal', function () {
	    		player.dispose();

	    	});
		}

    };

});