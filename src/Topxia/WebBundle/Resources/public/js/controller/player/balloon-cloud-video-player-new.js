define(function(require, exports, module) {

    var Widget = require('widget');
    require("video-player-new");

    var BalloonCloudVideoPlayer = Widget.extend({
        attrs: {
            url: '',
            fingerprint: '',
            watermark: '',
            timelimit: 10,
            remeberLastPos: true,
            disableControlBar: false,
            disableProgressBar: false,
            questions: []
        },

        events: {},

        setup: function() {
            var elementId = this.element.attr("id");
            var self = this;

            var extConfig = {};

            if(self.get('watermark') != '') {
                extConfig = $.extend(extConfig, {
                    watermark: {
                        file: self.get('watermark'),
                        pos : 'top.right',//top.right, bottom.right, bottom.left, center
                        xrepeat: 0,
                        opacity: 0.5
                    }
                });
            }

            if(self.get('fingerprint') != '') {
                extConfig = $.extend(extConfig, {
                    fingerprint: {
                        html: self.get('fingerprint'),
                        duration: 100
                    }
                })
            }

            if(self.get('timelimit') != '') {
                extConfig = $.extend(extConfig, {
                    pluck: {
                        timelimit: self.get('timelimit'),
                        text: "免费试看结束，购买后可完整观看",
                        display: true
                    }
                })
            }

            if(self.get('questions') != '' && self.get('questions').length > 0) {
                extConfig = $.extend(extConfig, {
                    exam: { 
                        popupExam : {
                            config : {
                                "mode" : "middle"
                            },
                            questions : self.get('questions')
                        }
                    }
                });
            }

            var playbackRates = {
              enable : true,
              source : 'hls',
              src : 'http://192.168.67.252/video-player/examples/server/playlist.m3u8'
            };
            var player = new VideoPlayerSDK($.extend({
                id: elementId,
                disableControlBar: self.get('disableControlBar'),
                disableProgressBar: self.get('disableProgressBar'),
                playlist: self.get('url'),
                remeberLastPos : self.get('remeberLastPos')
                // playbackRates : playbackRates
            }, extConfig));

            console.log($.extend({
                id: elementId,
                disableControlBar: self.get('disableControlBar'),
                disableProgressBar: self.get('disableProgressBar'),
                playlist: self.get('url'),
                remeberLastPos : self.get('remeberLastPos')
            }, extConfig));

            player.on('ready', function(e){
                self.trigger("ready", e);
            });

            player.on("timeupdate", function(e){
                self.trigger("timechange", e);
            });

            player.on("ended", function(e){
                self.trigger("ended", e);
            });

            player.on("playing", function(e){
                self.trigger("playing", e);
            });

            player.on("paused", function(e){
                self.trigger("paused", e);
            });

            player.on("answered", function(e){
                self.trigger("answered", e);
            });
            
            self.set('player', player);

            BalloonCloudVideoPlayer.superclass.setup.call(self);

            window.BalloonPlayer = this;
        },

        play: function(){
            this.get("player").play();
        },

        pause: function(){
            this.get("player").pause();
        },

        isPlaying: function() {
            if(this.get("player") && this.get("player").paused){
                return !this.get("player").paused();
            }
            return false;
        }

    });

    module.exports = BalloonCloudVideoPlayer;
});