import LocalVideoPlayer from './local-video-player';
import BalloonVideoPlayer from './balloon-cloud-video-player';
import AudioPlayer from './audio-player';

class PlayerFactory {

  static create(type, options) {
    switch (type) {
    case 'local-video-player':
      return new BalloonVideoPlayer(options);
    case 'audio-player':
      return new AudioPlayer(options);
    case 'balloon-cloud-video-player':
      return new BalloonVideoPlayer(options);
    }
  }
}

export default PlayerFactory;
