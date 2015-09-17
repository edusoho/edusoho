define(function(require, exports, module) {
    var VideoJS = require('video-js'),
        swfobject = require('swfobject');

    require('mediaelementplayer');

    var MediaPlayer = require('topxiawebbundle/controller/widget/media-player4');
    var SlidePlayer = require('topxiawebbundle/controller/widget/slider-player');
    var DocumentPlayer = require('topxiawebbundle/controller/widget/document-player');

    exports.run = function() {

        if ($("#lesson-preview-video-player").length > 0) {

            if ($("#lesson-preview-video-player").data('hlsUrl')) {

                $("#lesson-preview-video-player").html('<div id="lesson-video-player"></div>');
                   
                var mediaPlayer = new MediaPlayer({
                   element: '#lesson-preview-video-player',
                   playerId: 'lesson-video-player',
                });
                
                var $hlsUrl = $("#lesson-preview-video-player").data('hlsUrl');
                mediaPlayer.setSrc($hlsUrl, 'video');
                mediaPlayer.play();
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

        if ($("#lesson-preview-doucment").length > 0) {

            var $player = $("#lesson-preview-doucment");

            var html = '<iframe id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'400px\'></iframe>';
            $("#lesson-preview-doucment").html(html);

            var player = new DocumentPlayer({
                element: '#lesson-preview-doucment',
                swfFileUrl: $player.data('swfUri'),
                pdfFileUrl: $player.data('pdfUri')
            });
                
        }

        if($("#lesson-preview-flash").length>0){
            var player = $("#lesson-preview-flash");
            var html = '<div id="lesson-swf-player" ></div>';
            $("#lesson-preview-flash").html(html);
            swfobject.embedSWF(player.data('url'), 
                'lesson-swf-player', '100%', '100%', "9.0.0", null, null, 
                {wmode:'opaque',allowFullScreen:'true'});
        }

    };

});