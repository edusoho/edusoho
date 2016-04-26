define(function(require, exports, module) {
    var swfobject = require('swfobject');

    var MediaPlayer = require('topxiawebbundle/controller/widget/media-player4');
    var SlidePlayer = require('topxiawebbundle/controller/widget/slider-player');
    var DocumentPlayer = require('topxiawebbundle/controller/widget/document-player');

    var players = {};

    players.video = function($player, params) {
        $player.html('<div id="file-video-player"></div>')

        var mediaPlayer = new MediaPlayer({
           element: $player,
           playerId: 'file-video-player',
        });
        
        mediaPlayer.setSrc(params.hls_playlist, 'video');
        mediaPlayer.play();
    }

    players.audio = function($player, params) {
        $player.html('<div id="file-audio-player"></div>')

        var mediaPlayer = new MediaPlayer({
           element: $player,
           playerId: 'file-audio-player',
        });
        
        mediaPlayer.setSrc(params.hls_playlist, 'video');
        mediaPlayer.play();
    }

    players.flash = function($player, params) {
        $player.html('<div id="file-flash-player"></div>');
        swfobject.embedSWF(
            params.url, 
            'file-flash-player', '100%', '100%', "9.0.0", null, null, 
            {wmode:'opaque',allowFullScreen:'true'}
        );
    }

    players.ppt = function($player, params) {
        $player.html('<div class="slide-player" style="min-height:500px;"><div class="slide-player-body loading-background"></div><div class="slide-notice"><div class="header">已经到最后一张图片了哦<button type="button" class="close">×</button></div></div><div class="slide-player-control clearfix"><a href="javascript:" class="goto-first"><span class="glyphicon glyphicon-step-backward"></span></a><a href="javascript:" class="goto-prev"><span class="glyphicon glyphicon-chevron-left"></span></a><a href="javascript:" class="goto-next"><span class="glyphicon glyphicon-chevron-right"></span></span></a><a href="javascript:" class="goto-last"><span class="glyphicon glyphicon-step-forward"></span></a><a href="javascript:" class="fullscreen"><span class="glyphicon glyphicon-fullscreen"></span></a><div class="goto-index-input"><input type="text" class="goto-index form-control input-sm" value="1">&nbsp;/&nbsp;<span class="total"></span></div></div></div>');
        var player = new SlidePlayer({
            element: '.slide-player',
            slides: params.slides
        });
    }

    players.document = function($player, params) {
        $player.html('<iframe id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'400px\'></iframe>');
        var player = new DocumentPlayer({
            element: $player,
            swfFileUrl: $player.data('swfuri'),
            pdfFileUrl: $player.data('pdfuri')
        });
    }

    exports.run = function() {
        $player = $('#file-player');
        if ($player.length > 0) {
            players[$player.data('type')]($player, $player.data());
        }
    }

});