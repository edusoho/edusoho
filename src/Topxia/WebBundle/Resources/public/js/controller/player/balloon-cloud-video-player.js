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
            markers: [{id:0,time:-10,text:'',finished:true}],
            starttime: '0',
            timelimit:'0',
            controlBarLock:false
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
                            duration: 100,
                            frequency: 10000
                        }
                    })
                }

                plugins = $.extend(plugins, {
                        markers: {
                            markers: self.get('markers'),
                            markerTip: {
                               display: false,
                               time: function(marker) {
                                 return marker==undefined ? -1:marker.time;
                               }
                            },
                            markerEscape: true,
                            onMarkerReached:function(marker,player){
                              if(self.isPlaying() ){
                                window.BalloonPlayer.trigger('onMarkerReached', marker.id);
                              }
                            }
                        }
                    });

                plugins = $.extend(plugins, {
                        progressTips: {
                        }
                    });


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

                    player.hotkeys({
                        volumeStep: 0.1,
                        alwaysCaptureHotkeys:true,
                        customKeys: {
                          // Create custom hotkeys
                          ctrldKey: {
                            key: function(e) {
                              // Toggle something with CTRL + D Key
                              return (e.ctrlKey && e.which === 68);
                            },
                            handler: function(player, options) {
                              // Using mute as an example
                              if (options.enableMute) {
                                player.muted(!player.muted());
                              }
                            }
                          }
                        }
                      });
                });

                player.on('changeRes', function() {
                    Cookie.set("currentRes", player.getCurrentRes());
                });

                player.on('userinactive', function() {
                    if(self.get('controlBarLock')!==false){
                        player.userActive(true);
                    }
                });

                player.on('loadedmetadata', function(e){
                    self.trigger("ready", e);
                });

                player.on("timeupdate", function(e){
                    self.trigger("timechange", e);
                    var currentTime = player.currentTime();
                    var timelimit = self.get('timelimit');
                    if(timelimit>0 && timelimit<currentTime){
                        self.isPlaying() && player.pause();
                        player.currentTime(timelimit);
                        player.pluck({
                            text: "免费试看结束，购买后可完整观看",
                            display:true
                        });
                    }
                });

                player.on("ended", function(e){
                    self.trigger("ended", e);
                });

                player.on("firstplay", function(e){
                    self.trigger("firstplay", e);
                });

                player.on("play", function(e){
                    self.trigger("playing", e);
                    player.pluck({
                        text: "",
                        display:false
                    });
                });

                player.on("pause", function(e){
                    self.trigger("paused", e);
                    player.pluck({
                        text: "",
                        display:false
                    });
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

        finishMarker: function(id,isFinish) {
            var player = this.get("player");
            var markers = player.markers.getMarkers();
            for(var key in markers) 
            {
                if(markers[key].id == id) {
                    markers[key].finished = isFinish;
                    var marker = markers[key];
                    player.markers.remove(key);
                    player.markers.add([marker]);
                    player.currentTime(parseFloat(markers[key].time)+0.5);
                    break;
                }
            }
            this.get("player").play();
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

        setMarkerEscepe: function(mode) {
            var player = this.get("player");
            player.markers.setMarkerEscape(mode);
        },

        destroy: function() {
            this.get("player").dispose();
        },

        setControlBarLock: function (bool){
            this.set("controlBarLock",bool);
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