import EsMessenger from '../../../common/messenger';
import ActivityEmitter from '../../activity/activity-emitter';
class VideoPlay {
  constructor(elment) {
    this.dom = $(elment);
    this.data = this.dom.data();
    this.player = {};
    this.emitter = new ActivityEmitter();
  }

  play() {
    var messenger = new EsMessenger({
      name: 'parent',
      project: 'PlayerProject',
      children: [],
      type: 'parent'
    });

    messenger.on("ended", (msg) => {
      this.player.playing = false;
      this._onFinishLearnTask(msg);
    });

    messenger.on("playing", (msg) => {
      this.player.playing = true;
    });

    messenger.on("paused", (msg) => {
      this.player.playing = false;
    });

    messenger.on("timechange", (msg) => {})
  }

  _onFinishLearnTask(msg) {
    this.emitter.emit('finish', { data: msg }).then(() => {
      console.log('audio.finish');
    }).catch((error) => {
      console.error(error);
    });
  }


}
let videoPlay = new VideoPlay("#audio-content");
videoPlay.play();
