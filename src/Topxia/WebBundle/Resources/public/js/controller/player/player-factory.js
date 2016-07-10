define(function(require, exports, module) {

    var Widget = require('widget');


    var PlayerFactory = Widget.extend({
        attrs: {
        },

        events: {
        },

        setup: function() {
        },

        create: function(type, options){
            switch(type){
                case "local-video-player":
                    var LocalVideoPlayer = require('./local-video-player');
                    return new LocalVideoPlayer(options);
                    break;
                case "balloon-cloud-video-player":
                    var BalloonVideoPlayer = require('./balloon-cloud-video-player-new');
                    return new BalloonVideoPlayer(options);
                    break;
                case "audio-player":
                    var AudioPlayer = require('./audio-player');
                    return new AudioPlayer(options);
                    break;
            }

        },

        destroy: function(){
        }
    });

    module.exports = PlayerFactory;

});