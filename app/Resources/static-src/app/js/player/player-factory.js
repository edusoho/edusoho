import LocalVideoPlayer from './local-video-player';
import BalloonVideoPlayer from './balloon-cloud-video-player';

class PlayerFactory {

  static create(type, options) {
    switch (type) {
    case 'local-video-player':
    case 'audio-player':
      return new LocalVideoPlayer(options);
    case 'balloon-cloud-video-player':
      return new BalloonVideoPlayer(options);
    }
  }
}
var a = 1;
export default PlayerFactory;
