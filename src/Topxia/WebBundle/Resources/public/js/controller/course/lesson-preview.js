define(function(require, exports, module) {

	var VideoJS = require('video-js'),
		swfobject = require('swfobject');

	require('mediaelementplayer');


    exports.run = function() {

		if ($("#lesson-preview-video-player").length > 0) {
			$("#lesson-preview-video-player").html('<video id="lesson-video-player" class="video-js vjs-default-skin" controls preload="auto"  width="100%" height="360"></video>');

			var videoPlayer = VideoJS("lesson-video-player", {
            	techOrder: ['html5','flash']
            });
            videoPlayer.width('100%');
            videoPlayer.src($("#lesson-preview-video-player").data('url'));
	    	videoPlayer.play();

	    	$('#modal').one('hidden.bs.modal', function () {
	    		videoPlayer.dispose();
	    		$("#lesson-preview-video-player").remove();
	    	});
		}

		if ($("#lesson-preview-audio-player").length > 0) {
			var audioPlayer = new MediaElementPlayer('#lesson-preview-audio-player',{
				mode:'auto_plugin',
				enablePluginDebug: false,
				enableAutosize:true,
				success: function(media) {
					media.play();
				}
			});

	    	$('#modal').one('hidden.bs.modal', function () {
	    		audioPlayer.remove();
	    		$("#lesson-preview-audio-player").remove();
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