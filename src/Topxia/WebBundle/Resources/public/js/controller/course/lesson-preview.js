define(function(require, exports, module) {

	var VideoJS = require('video-js'),
		swfobject = require('swfobject');

	require('mediaelementplayer');

	var MediaPlayer = require('../widget/media-player4');
	var SlidePlayer = require('../widget/slider-player');
    var DocumentPlayer = require('../widget/document-player');

    exports.run = function() {

		if ($("#lesson-preview-video-player").length > 0) {

			if ($("#lesson-preview-video-player").data('hlsUrl')) {

		        $("#lesson-preview-video-player").html('<div id="lesson-video-player"></div>');
			        
        		var mediaPlayer = new MediaPlayer({
        			element: '#lesson-preview-video-player',
        			playerId: 'lesson-video-player',
        			height: '360px'
        		});
                var $hlsUrl = $("#lesson-preview-video-player").data('hlsUrl');
                if ($("#lesson-preview-video-player").data('timelimit')) {
                    $("#lesson-preview-video-player").append($('.js-buy-text').html());

                    mediaPlayer.on('ended', function() {
                        $('#lesson-preview-video-player').html($('.js-time-limit-dev').html());
                    });
                }

        		mediaPlayer.setSrc($hlsUrl, 'video');
        		mediaPlayer.play();

                $('#modal').one('hidden.bs.modal', function () {
                    mediaPlayer.dispose();
                });

			} else {
				$("#lesson-preview-video-player").html('<video id="lesson-video-player" class="video-js vjs-default-skin" controls preload="auto"  width="100%" height="360"></video>');

				var videoPlayer = VideoJS("lesson-video-player", {
	            	techOrder: ['flash','html5']
	            });
	            videoPlayer.width('100%');
	            videoPlayer.src($("#lesson-preview-video-player").data('url'));
		    	videoPlayer.play();

		    	$('#modal').one('hidden.bs.modal', function () {
		    		videoPlayer.dispose();
		    		$("#lesson-preview-video-player").remove();
		    	});
			}

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

		if ($("#lesson-preview-ppt-player").length > 0) {
			var $player = $("#lesson-preview-ppt-player");
			var html = '';
            $.get($player.data('url'), function(response) {
                if (response.error) {
                    html = '<div class="lesson-content-text-body text-danger">' + response.error.message + '</div>';
                } else {
	                html = '<div class="slide-player" style="min-height:500px;"><div class="slide-player-body loading-background"></div><div class="slide-notice"><div class="header">已经到最后一张图片了哦<button type="button" class="close">×</button></div></div><div class="slide-player-control clearfix"><a href="javascript:" class="goto-first"><span class="glyphicon glyphicon-step-backward"></span></a><a href="javascript:" class="goto-prev"><span class="glyphicon glyphicon-chevron-left"></span></a><a href="javascript:" class="goto-next"><span class="glyphicon glyphicon-chevron-right"></span></span></a><a href="javascript:" class="goto-last"><span class="glyphicon glyphicon-step-forward"></span></a><a href="javascript:" class="fullscreen"><span class="glyphicon glyphicon-fullscreen"></span></a><div class="goto-index-input"><input type="text" class="goto-index form-control input-sm" value="1">&nbsp;/&nbsp;<span class="total"></span></div></div></div>';
                }

                $player.html(html);

                if (!response.error) {
                    var player = new SlidePlayer({
                        element: '.slide-player',
                        slides: response
                    });
                }

            }, 'json');
		}


        if($("#lesson-preview-flash").length>0){
            var player = $("#lesson-preview-flash");
            $.get(player.data('url'), function(response) {
                var html = '<div id="lesson-swf-player" ></div>';
                $("#lesson-preview-flash").html(html);
                swfobject.embedSWF(response.mediaUri, 
                    'lesson-swf-player', '100%', '100%', "9.0.0", null, null, 
                    {wmode:'opaque',allowFullScreen:'true'});
            });
            player.css("height", '360px');
        }

        if ($("#lesson-preview-doucment").length > 0) {

            var $player = $("#lesson-preview-doucment");
            $.get($player.data('url'), function(response) {
                if (response.error) {
                    var html = '<div class="lesson-content-text-body text-danger">' + response.error.message + '</div>';
                    $("#lesson-preview-doucment").html(html);
                    return ;
                }

                var html = '<iframe id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'400px\'></iframe>';
                $("#lesson-preview-doucment").html(html);

                var watermarkUrl = $("#lesson-preview-doucment").data('watermarkUrl');
                if (watermarkUrl) {
                    $.get(watermarkUrl, function(watermark) {
                        var player = new DocumentPlayer({
                            element: '#lesson-preview-doucment',
                            swfFileUrl:response.swfUri,
                            pdfFileUrl:response.pdfUri,
                            watermark: {
                                'xPosition': 'center',
                                'yPosition': 'center',
                                'rotate': 45,
                                'contents': watermark
                            }
                        });
                    });
                } else {
                    var player = new DocumentPlayer({
                        element: '#lesson-preview-doucment',
                        swfFileUrl:response.swfUri,
                        pdfFileUrl:response.pdfUri
                    });
                }
            }, 'json');
        }

		if ($("#lesson-preview-swf-player").length > 0) {
			swfobject.embedSWF($("#lesson-preview-swf-player").data('url'), 'lesson-preview-swf-player', '100%', '360', "9.0.0", null, null, {wmode: 'transparent'});

	    	$('#modal').one('hidden.bs.modal', function () {
	    		swfobject.removeSWF('lesson-preview-swf-player');
	    	});
		}

        if ($("#lesson-preview-iframe").length > 0) {

            var html = '<iframe src="' + $("#lesson-preview-iframe").data('url') + '" style="height:360px; width:100%; border:0px;" scrolling="no"></iframe>';
            $("#lesson-preview-iframe").html(html).show();

            $('#modal').one('hidden.bs.modal', function () {
                $("#lesson-preview-iframe").remove();
            });
        }

		$modal = $('#modal');
        $modal.on('click','.js-buy-btn', function(){
			$.get($(this).data('url'), function(html) {
				$modal.html(html);
				$('#join-course-btn').click();
			});
		});

    };

});