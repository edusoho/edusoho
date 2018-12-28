import swfobject from 'es-swfobject';
import EsMessenger from 'app/common/messenger';
import { getSupportedPlayer } from 'common/video-player-judge';
import ActivityEmitter from 'app/js/activity/activity-emitter';

export default class VideoPlay {
  constructor(recorder) {
    this.player = {};
    this.intervalId = null;
    this.recorder = recorder;
    this.emitter = new ActivityEmitter();
  }

  play() {
    if ($('#swf-player').length) {
      this.flashTip();
      this._playerSwf();
    } else {
      if (getSupportedPlayer() === 'flash') {
        this.flashTip(true);
      } else {
        this._playVideo();
      }
    }
    this.record();
  }


  flashTip(flag) {
    if (!swfobject.hasFlashPlayerVersion('11')) {
      const html = `
      <div class="alert alert-warning alert-dismissible fade in" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
        </button>
        ${Translator.trans('site.flash_not_install_hint')}
      </div>`;
      $('#video-content').html(html).show();
    } else {
      if (flag) {
        this._playVideo();
      }
    }
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
      swf_dom, '100%', '100%', '9.0.0', null, null, {
        wmode: 'opaque',
        allowFullScreen: 'true'
      });
  }

  _playVideo() {
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
      this.recorder.watching(this.emitter);
    });

    messenger.on('timechange', (msg) => {
      this.player.currentTime = msg.currentTime;
    });
  }

  _onFinishLearnTask(msg) {
    this.emitter.emit('finish', { data: msg }).then(() => {
      clearInterval(this.intervalId);
    }).catch((error) => {
      console.error(error);
    });
  }

}