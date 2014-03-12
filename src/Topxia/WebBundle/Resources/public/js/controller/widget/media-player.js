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
                javascriptCallbackFunction: "GrindPlayerEventProcesser",
                autoPlay:true
            };

            if (this.get('src').indexOf('.m3u8') > 0 || this.get('src').indexOf('HLSQualitiyList') > 0) {
                // flashvars.plugin_hls = "http://cdn.staticfile.org/GrindPlayer/1.0.0/HLSProviderOSMF.swf";
                flashvars.plugin_hls = "http://hlstest.qiniudn.com/HLSProviderOSMF.swf";
            }
// 1000
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
                // "http://cdn.staticfile.org/GrindPlayer/1.0.0/GrindPlayer.swf",
                "http://hlstest.qiniudn.com/GrindPlayer.swf",
                this.get('playerId'),
                this.get('width'),  this.get('height') , "10.2", null, flashvars, params, attrs
            );
        },

        _evetProcesser: function(playerId, event, data) {
            var firstload= true;
            switch(event) {
                case "onJavaScriptBridgeCreated":
                    console.log('onJavaScriptBridgeCreated');
                    this.set('flashPlayer', document.getElementById(playerId));
                    break;
                // case "ready":
                //     console.log('ready');
                //     if(firstload){
                //         console.log('firstloadb');
                //         var player = this.get('flashPlayer');
                //         //这个必须调用，否则播放器的播放按钮和播放状态会处于不同步状态
                //         console.log('firstload 1');
                //         player.play2();
                //         console.log('firstload 2');
                //         //跳转到某个时间，并进行播放
                //         player.seek(10);
                //         //一定设置这个值，避免播放完后重复跳转
                //         firstload = false;
                //         console.log('firstload end');
                //     }
                //     break;
                case "complete":
                    this.trigger('ended');
                    break;
            }
        }

    });

    module.exports = MediaPlayer;
});