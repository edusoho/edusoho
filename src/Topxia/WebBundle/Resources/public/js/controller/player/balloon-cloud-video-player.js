define(function(require, exports, module) {

    var Widget = require('widget');
    require("video-player");
    var Cookie = require('cookie');

    var BalloonCloudVideoPlayer = Widget.extend({
        attrs: {
            fingerprint: '',
            watermark: '',
            url: '',
            dynamicSource: '',
            markers: [],
            starttime: '0'
        },

        events: {},

        setup: function() {
            var elementId = this.element.attr("id");
            var self = this;

            videojs.options.flash.flashVars = {
                hls_debug: false,
                hls_debug2: false,
                hls_seekmode: "ACCURATE"
            };

            $.get(self.get('url'), function(playlist) {

                var plugins = {};

                if(self.get('watermark') != '') {
                    plugins = $.extend(plugins, {
                        watermark: {
                            file: self.get('watermark'),
                            xpos: 100,
                            ypos: 0,
                            xrepeat: 0,
                            opacity: 0.5
                        }
                    });
                }

                if(self.get('fingerprint') != '') {
                    plugins = $.extend(plugins, {
                        fingerprint: {
                            html: self.get('fingerprint'),
                            duration: 5000
                        }
                    })
                }

                var player = videojs(elementId, {
                    techOrder: ["flash", "html5"],
                    controls: true,
                    autoplay: false,
                    preload: 'none',
                    starttime: self.get('starttime'),
                    language: 'zh-CN',
                    width:'100%',
                    height:'100%',
                    plugins: plugins
                });

                player.ready(function() {
                    var resArray = [];
                    $.each(playlist, function(i, source) {
                        resArray.push(source.name);
                        player.options().sources.push({'type': 'video/mp4', 'src': source.src, 'data-res': source.name, 'data-level': source.level});
                    });
                    var currentRes = Cookie.get("currentRes");
                    if(currentRes == undefined){
                        resArray.reverse();
                        currentRes = resArray.join(",");
                    }
                    player.resolutionSelector({
                        default_res : currentRes,
                        dynamic_source : self.get('url')
                    });
                });

                player.on('changeRes', function() {
                    Cookie.set("currentRes", player.getCurrentRes());
                });

                player.on('loadedmetadata', function(e){
                    self.trigger("ready", e);
                });

                player.on("timeupdate", function(e){
                    self.trigger("timechange", e);
                });

                player.on("ended", function(e){
                    self.trigger("ended", e);
                });

                player.on("firstplay", function(e){
                    self.trigger("firstplay", e);
                });

                player.on("play", function(e){
                    self.trigger("playing", e);
                });

                player.on("pause", function(e){
                    self.trigger("paused", e);
                });
                

                self.set('player', player);

                BalloonCloudVideoPlayer.superclass.setup.call(self);

            }, 'json');
            
            window.BalloonPlayer = this;
        },

        play: function(){
            this.get("player").play();
        },

        pause: function(){
            this.get("player").pause();
        },

        _onEnded: function(e) {
            var player = this.get("player");

            player.currentTime(0);
            player.pause();
        },

        getCurrentTime: function() {
            return this.get("player").currentTime();
        },

        getDuration: function() {
            return this.get("player").duration();
        },

        getMarkers: function() {
            return this.get('markers');
        },

        setMarkers: function(markers) {
            var player = this.get("player");
            player.markers.reset(markers);
        },

        addMarker: function(marker) {
            var player = this.get("player");
            player.markers.add(marker);
        },

        setCurrentTime: function(time) {
            this.get("player").currentTime(time);
        },

        isPlaying: function() {
            if(this.get("player") && this.get("player").paused){
                return !this.get("player").paused();
            }
            return false;
        },

        destroy: function() {
            this.get("player").dispose();
        },

        durationFormat: function(secondTime) {
            var minutes = parseInt(secondTime / 60);
            var seconds = secondTime - minutes * 60;
            return this.pad(minutes,2) + ':' +this.pad(seconds,2);
        },

        pad: function(num,len) {
            return (new Array(len >(''+num).length ? (len - (''+num).length+1) : 0).join('0') + num);
        }
        


    });

    module.exports = BalloonCloudVideoPlayer;
});