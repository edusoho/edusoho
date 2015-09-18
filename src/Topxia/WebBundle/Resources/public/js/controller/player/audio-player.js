define(function(require, exports, module) {

    var MediaPlayer = require('./media-player');
    var Notify = require('common/bootstrap-notify');
    var Widget = require('widget');
    require('mediaelementplayer');

    var AudioPlayer = MediaPlayer.extend({
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
                    media.addEventListener("ended", function(e) {
                        that.trigger("ended", e);
                    });
                    media.play();
                }
            });
            this.set('player', audioPlayer);
        }
    });

    module.exports = AudioPlayer;
});
