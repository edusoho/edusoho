define(function(require, exports, module) {
	var VideoJS = require('video-js'),
		swfobject = require('swfobject');


	var SlidePlayer = require('../widget/slider-player');
    var DocumentPlayer = require('../widget/document-player');
    var Messenger = require('../player/messenger');

    exports.run = function() {

		if ($("#lesson-preview-player").length > 0) {
            var lessonVideoDiv = $('#lesson-preview-player');
            
            var courseId = lessonVideoDiv.data("courseId");
            var lessonId = lessonVideoDiv.data("lessonId");
            var playerUrl = lessonVideoDiv.data("playerUrl");

            var html = '<iframe src=\''+playerUrl+'\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';

            lessonVideoDiv.html(html);

            if (lessonVideoDiv.data('timelimit')) {
                $(".modal-footer").prepend($('.js-buy-text').html());
                var messenger = new Messenger({
                    name: 'parent',
                    project: 'PlayerProject',
                    children: [viewerIframe],
                    type: 'parent'
                });

                messenger.on("ended", function(){
                    lessonVideoDiv.html($('.js-time-limit-dev').html());
                });
            }
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
            if (!swfobject.hasFlashPlayerVersion('11')) {
                var html = '<div class="alert alert-warning alert-dismissible fade in" role="alert">';
                html += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                html += '<span aria-hidden="true">×</span>';
                html += '</button>';
                html += '您的浏览器未装Flash播放器或版本太低，请先安装Flash播放器。请点击<a target="_blank" href="http://www.adobe.com/go/getflashplayer">这里</a>安装</p></div>';
                html += '</div>';
                player.html(html);
            } else {
                $.get(player.data('url'), function(response) {
                    var html = '<div id="lesson-swf-player" ></div>';
                    $("#lesson-preview-flash").html(html);
                    swfobject.embedSWF(response.mediaUri, 
                        'lesson-swf-player', '100%', '100%', "9.0.0", null, null, 
                        {wmode:'opaque',allowFullScreen:'true'});
                });
            }
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
                            swfFileUrl:response.swf,
                            pdfFileUrl:response.pdf,
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
                        swfFileUrl:response.swf,
                        pdfFileUrl:response.pdf
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
        $modal.on('hidden.bs.modal', function(){
            if ($("#lesson-preview-player").length > 0) {
                $("#lesson-preview-player").html("");
            }
        });
        $modal.on('click','.js-buy-btn', function(){
            $.get($(this).data('url'), function(html) {
                $modal.html(html);
            });
        });
    };

});