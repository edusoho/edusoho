import swfobject from 'es-swfobject';
import EsMessenger from '../../../common/messenger';
import ActivityEmitter from '../../activity/activity-emitter';
import 'store';
class VideoPlay {
  constructor(recorder) {
    this.player = {};
    this.intervalId = null;
    this.recorder = recorder;
    this.emitter = new ActivityEmitter();
  }

  play() {
    if ($('#swf-player').length) {
      this._playerSwf();
    } else {
      this._playVideo();
    }
    this.record();
  }

  record() {
    this.intervalId = setInterval(() => {
      this.recorder.addVideoPlayerCounter(this.emitter, this.player);
    }, 1000);
  }

  getPlay() {
    return this.player;
  }

  _playerSwf() {
    const swf_dom = 'swf-player';
    swfobject.embedSWF($('#' + swf_dom).data('url'),
      swf_dom, '100%', '100%', "9.0.0", null, null, {
        wmode: 'opaque',
        allowFullScreen: 'true'
      });
  }

  _playVideo() {
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

    messenger.on("timechange", (msg) => {
      this.player.currentTime = msg.currentTime;
    })
  }

  _onFinishLearnTask(msg) {
    this.emitter.emit('finish', {data: msg}).then(() => {
      console.log('vidoe.finish');
      clearInterval(this.intervalId)
    }).catch((error) => {
      console.error(error);
    });
  }

}


class VideoRecorder {
  constructor(container) {
    this.container = container;
    this.interval = 120;
  }

  addVideoPlayerCounter(emitter, player) {
    let $container = $(this.container);
    let activityId = $container.data('id');
    let playerCounter = store.get("activity_id_" + activityId + "_playing_counter");
    if (!playerCounter) {
      playerCounter = 0;
    }
    if (!(player && player.playing)) {
      return false;
    }
    console.log(playerCounter, this.interval)
    if (playerCounter >= this.interval) {
      emitter.emit('watching', {watchTime: this.interval}).then(() => {
      }).catch((error) => {
        console.error(error);
      });
      playerCounter = 0;
    } else if (player.playing) {
      playerCounter++;
    }
    store.set("activity_id_" + activityId + "_playing_counter", playerCounter);
  }

}

let recorder = new VideoRecorder('#video-content');
let videoplay = new VideoPlay(recorder);
videoplay.play();
