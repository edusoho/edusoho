define(function(require, exports, module) {

    var Widget = require('widget');
    var swfobject = require('swfobject');

    var MediaPlayer = Widget.extend({
        attrs: {
            src: '',
            srcType: '',
            width: '100%',
            height: '100%',
            _firstPlay: true,
            runtime: null
        },

        events: {},

        setup: function() {
            window.__MediaPlayerEventProcesser = this._evetProcesser;
            window.__MediaPlayer = this;
        },

        setSrc: function(src, type) {
            this.set('src', src);
            this.set('srcType', type);
        },

        play: function() {

            if (swfobject.hasFlashPlayerVersion('10.2')) {
                this._initGrindPlayer();
            } else if (this._isSupportHtml5Video()) {
                this._initHtml5Player();
            } else {
                alert('您的浏览器未装Flash播放器或版本太低，请先安装Flash播放器。');
            }

        },

        dispose: function() {
            var runtime = this.get('runtime');
            if (runtime == 'flash') {
                swfobject.removeSWF(this.get('playerId'));
            } else if (runtime == 'html5') {
                $("#" + this.get('playerId')).remove();
            }
        },

        _initHtml5Player: function() {
            var style= "width:" + this.get('width') + ';height:' + this.get('height');
            var html = '<video id="' + this.get('playerId') + '" src="';
            html += this.get('src') + '" autoplay controls style="' + style + '">';
            html += '</video>';
            this.element.html(html);
            this.set('runtime', 'html5');
        },

        _isSupportHtml5Video: function() {
            return !!document.createElement('video').canPlayType;
        },

        _initGrindPlayer: function() {
            var flashvars = {
                src: encodeURIComponent(this.get('src')),
                javascriptCallbackFunction: "__MediaPlayerEventProcesser",
                autoPlay:false,
                autoRewind: false,
                loop:false,
                bufferTime: 8
            };

            if (this.get('src').indexOf('.m3u8') > 0 || this.get('src').indexOf('HLSQualitiyList') > 0) {
                flashvars.plugin_hls = app.httpHost + app.basePath + "/assets/libs/player/flashls-0.4.0.3.swf";
                flashvars.hls_maxbackbufferlength = 300;
            }
            
            if (this.element.data('watermark') && $('#videoWatermarkEmbedded').val() !=1) {
                flashvars.plugin_watermake = app.config.cloud.video_player_watermark_plugin;
                flashvars.watermake_namespace = 'watermake';
                flashvars.watermake_url = this.element.data('watermark');
            }

            if (this.element.data('fingerprint')) {
                flashvars.plugin_fingerprint = app.config.cloud.video_player_fingerprint_plugin;
                flashvars.fingerprint_namespace = 'fingerprint';
                flashvars.fingerprint_src = this.element.data('fingerprint');
            }

            var params = {
                wmode:'opaque',
                allowFullScreen: true
                , allowScriptAccess: "always"
                , bgcolor: "#000000"
            };

            var attrs = {
                name: this.get('playerId')
            };

            swfobject.embedSWF(
                app.config.cloud.video_player,
                this.get('playerId'),
                this.get('width'),  this.get('height') , "10.2", null, flashvars, params, attrs
            );
            this.set('runtime', 'flash');
        },

        _evetProcesser: function(playerId, event, data) {
            var firstload= true;
            switch(event) {
                case "onJavaScriptBridgeCreated":
                    break;
                case "ready":
                    if(window.__MediaPlayer.get('_firstPlay')) {
                        var player = document.getElementById(playerId);
                        player.play2();
                        window.__MediaPlayer.trigger('ready',playerId, data);
                        window.__MediaPlayer.set('_firstPlay', false);
                    }
                    break;
                case "complete":
                    window.__MediaPlayer.trigger('ended');
                    break;
                case "timeChange":
                    window.__MediaPlayer.trigger('timeChange',data);
                    break;
                case "playing":
                    window.__MediaPlayer.trigger('playing');
                    break;
                case "paused":
                    window.__MediaPlayer.trigger('paused');
                    break;
            }
        }

    });

    module.exports = MediaPlayer;
});