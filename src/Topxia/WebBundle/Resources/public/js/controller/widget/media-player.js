define(function(require, exports, module) {

    var Widget = require('widget');
    var swfobject = require('swfobject');

    var MediaPlayer = Widget.extend({
        attrs: {
            src: '',
            srcType: '',
            width: '100%',
            height: '100%',
        },

        events: {},

        setup: function() {
            window.GrindPlayerEventProcesser = this._evetProcesser;
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

        _initHtml5Player: function() {
            var style= "width:" + this.get('width') + ';height:' + this.get('height');
            var html = '<video id="' + this.get('playerId') + '" src="';
            html += this.get('src') + '" autoplay controls style="' + style + '">';
            html += '</video>';
            this.element.html(html);
        },

        _isSupportHtml5Video: function() {
            return !!document.createElement('video').canPlayType;
        },

        _initGrindPlayer: function() {
            var flashvars = {
                src: encodeURIComponent(this.get('src')),
                javascriptCallbackFunction: "GrindPlayerEventProcesser"
            };

            if (this.get('src').indexOf('.m3u8') > 0) {
                flashvars.plugin_hls = "http://cdn.staticfile.org/GrindPlayer/1.0.0/HLSProviderOSMF.swf"
            }

            var params = {
                wmode:'opaque',
                allowFullScreen: true
                , allowScriptAccess: "always"
                , bgcolor: "#000000"
            };

            var attrs = {
                name: "player"
            };

            swfobject.embedSWF(
                "http://cdn.staticfile.org/GrindPlayer/1.0.0/GrindPlayer.swf",
                this.get('playerId'),
                this.get('width'),  this.get('height') , "10.2", null, flashvars, params, attrs
            );
        },

        _evetProcesser: function(playerId, event, data) {
            switch(event) {
                case "onJavaScriptBridgeCreated":
                    this.set('flashPlayer', document.getElementById(playerId));
                    break;
                case "complete":
                    this.trigger('ended');
                    break;
            }
        }

    });

    module.exports = MediaPlayer;
});