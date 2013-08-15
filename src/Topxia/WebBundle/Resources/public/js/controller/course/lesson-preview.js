define(function(require, exports, module) {

	var VideoJS = require('video-js'),
		swfobject = require('swfobject');

    exports.run = function() {

		if ($("#lesson-preview-video-player").length > 0) {
			var player = VideoJS("lesson-preview-video-player");
	    	player.play();

	    	$('#modal').one('hidden.bs.modal', function () {
	    		player.dispose();
	    	});
		}

		if ($("#lesson-preview-swf-player").length > 0) {
			swfobject.embedSWF($("#lesson-preview-swf-player").data('url'), 'lesson-preview-swf-player', '100%', '360', "9.0.0");

	    	$('#modal').one('hidden.bs.modal', function () {
	    		swfobject.removeSWF('lesson-preview-swf-player');
	    	});
		}

    };

});