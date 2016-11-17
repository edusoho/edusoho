import  LocalVideoPlayer from './local-video-player';
import  BalloonVideoPlayer from './balloon-cloud-video-player-new';
import  AudioPlayer from './audio-player';

class PlayerFactory {

   static create(type, options) {
        switch (type) {
            case "local-video-player":
                return new LocalVideoPlayer(options);
                break;
            case "balloon-cloud-video-player":
                return new BalloonVideoPlayer(options);
                break;
            case "audio-player":
                var AudioPlayer = require('./audio-player');
                return new AudioPlayer(options);
                break;
        }
    }

}

export default PlayerFactory;