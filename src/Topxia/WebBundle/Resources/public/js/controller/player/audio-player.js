define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    require('mediaelementplayer');

    var AudioPlayer = Widget.extend({
    	attrs: {
        },

        events: {
        },

        setup: function() {
        	var that = this;
        	var audioPlayer = new MediaElementPlayer('#'+this.element.attr('id'), {
                mode:'auto_plugin',
                enablePluginDebug: false,
                enableAutosize:true,
                success: function(media) {
                    media.addEventListener("pause", function(e) {
                        that.trigger("paused", e);
                    });
                    media.addEventListener("play", function(e) {
                        that.trigger("playing", e);
                    });
                    media.addEventListener('loadedmetadata', function(e){
                        that.trigger("ready", e);
                    });
                    media.addEventListener("ended", function(e) {
                        that.trigger("ended", e);
                    });

                    media.play();
                }
            });

            this.set('player', audioPlayer);
        },

        play: function(){
            if(this.get("player").paused){
                this.get("player").play();
            }
        },
        setCurrentTime:function(){

        },
        pause: function(e) {
            var player = this.get("player");
            player.pause();
        }


    });

    module.exports = AudioPlayer;
});
