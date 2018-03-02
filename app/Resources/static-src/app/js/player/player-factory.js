import LocalVideoPlayer from './local-video-player';
import BalloonVideoPlayer from './balloon-cloud-video-player';

class PlayerFactory {

	static create(type, options) {
		switch (type) {
		case 'local-video-player':
		case 'audio-player':
			new LocalVideoPlayer(options);
			break;
		case 'balloon-cloud-video-player':
			new BalloonVideoPlayer(options);
			break;
		}
	}
}

export default PlayerFactory;
