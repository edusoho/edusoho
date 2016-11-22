import  LocalVideoPlayer from './local-video-player';
import  BalloonVideoPlayer from './balloon-cloud-video-player-new';

class PlayerFactory {

    static create(type, options) {
        switch (type) {
            case "local-video-player":
            case "audio-player":
                return new LocalVideoPlayer(options);
                break;
            case "balloon-cloud-video-player":
                return new BalloonVideoPlayer(options);
                break;
        }
    }

}

export default PlayerFactory;