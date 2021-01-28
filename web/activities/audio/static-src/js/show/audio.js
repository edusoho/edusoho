import EsMessenger from 'app/common/messenger';
import ActivityEmitter from 'app/js/activity/activity-emitter';

export default class AudioPlay {
  constructor(elment, recorder) {
    this.dom = $(elment);
    this.data = this.dom.data();
    this.recorder = recorder;
    this.player = {};
    this.emitter = new ActivityEmitter();
  }

  record() {
    this.intervalId = setInterval(() => {
      this.recorder.addAudioPlayerCounter(this.emitter, this.player);
    }, 1000);
  }

  play() {
    var messenger = new EsMessenger({
      name: 'partner',
      project: 'PlayerProject',
      children: [],
      type: 'parent'
    });

    messenger.on('ended', (msg) => {
      this.player.playing = false;
      this._onFinishLearnTask(msg);
    });

    messenger.on('playing', (msg) => {
      this.player.playing = true;
    });

    messenger.on('paused', (msg) => {
      this.player.playing = false;
    });

    messenger.on('timechange', (msg) => {});

    this.record();
  }

  _onFinishLearnTask(msg) {
    this.emitter.emit('finish', { data: msg }).then(() => {
      console.log('audio.finish');
    }).catch((error) => {
      console.error(error);
    });
  }
}