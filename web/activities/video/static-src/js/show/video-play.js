import swfobject from 'es-swfobject';
import EsMessenger from 'app/common/messenger';
import { getSupportedPlayer } from 'common/video-player-judge';
import ActivityEmitter from 'app/js/activity/activity-emitter';
import LocalVideoPlayer from 'app/js/player/local-video-player';
import 'store';

export default class VideoPlay {
  constructor(recorder) {
    this.player = {};
    this.intervalId = null;
    this.recorder = recorder;
    this.emitter = new ActivityEmitter();
  }

  play() {
    this['activity_id_' + this.recorder.activityId + '_finish'] = false;

    if ($('#local-video-player').length) {
      this._playerLocalVideo();
    }else if ($('#swf-player').length) {
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

  _playerLocalVideo() {
    $('#lesson-video-content').html('<video id="lesson-player" style="width: 100%;height: 100%;" class="video-js vjs-default-skin" controls preload="auto"></video>');
    new LocalVideoPlayer({
      'element' : 'lesson-player',
      'url' : $('#lesson-video-content').data('url'),
    });
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
      if (this['activity_id_' + this.recorder.activityId + '_finish'] && this.player.playing){
        this._onRestartLearnTask(msg);
      }
      this.player.currentTime = msg.currentTime;
    });
  }

  _onFinishLearnTask(msg) {
    this['activity_id_' + this.recorder.activityId + '_finish'] = true;
    this.emitter.emit('finish', { data: msg }).then(() => {
      clearInterval(this.intervalId);
      store.set('activity_id_' + this.recorder.activityId + '_playing_counter', 0);
    }).catch((error) => {
      console.error(error);
    });
  }

  _onRestartLearnTask(msg) {
    this['activity_id_' + this.recorder.activityId + '_finish'] = false;
    this.record();
    this.emitter.emit('restart', true).then(() => {
    }).catch((error) => {
      console.error(error);
    });
  }

}
