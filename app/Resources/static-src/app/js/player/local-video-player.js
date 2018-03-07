import videojs from 'video.js';
import Emitter from 'component-emitter';
import notify from 'common/notify';
let videoSwf = require('video.js/dist/video-js/video-js.swf');

class LocalVideoPlayer extends Emitter {
  constructor(options) {
    super();
    this.options = options;
    this.player = {};
    this.setup();
  }

  setup() {
    var techOrder = ['flash', 'html5'];
    if (this.options.agentInWhiteList || this.options.mediaType == 'audio') {
      techOrder = ['html5', 'flash'];
    }
    var that = this;
    var player = videojs(this.options.element, {
      techOrder: techOrder,
      loop: false,
      flash: {
        swf: videoSwf
      },
      controlBar: {
        liveDisplay: false
      }
    });

    player.dimensions('100%', '100%');
    player.src(this.options.url);

    player.on('error', error => {
      player.hasPlayerError = true;
      var message = Translator.trans('site.browser_useless_play_video_hint');
      notify('danger',message, {delay:30000});
    });

    player.on('fullscreenchange', function(e) {
      if ($(e.target).hasClass('vjs-fullscreen')) {
        $('#site-navbar').hide();
      }
    });

    player.on('ended', (e) => {
      this.emit('ended', e);
      this._onEnded(e);
    });

    player.on('timeupdate', (e) => {
      this.emit('timechange', e);
    });

    player.on('loadedmetadata', (e) => {
      that.emit('ready', e);
    });

    player.on('play', (e) => {
      that.emit('playing', e);
    });

    player.on('pause', (e) => {
      that.emit('paused', e);
    });

    this.player = player;
  }

  checkHtml5() {
    if (window.applicationCache) {
      return true;
    } else {
      return false;
    }
  }


  play() {
    this.player.play();
  }

  _onEnded(e) {
    this.player.pause();
    this.player.currentTime(0);
  }


  getCurrentTime() {
    return this.player.currentTime();
  }


  getDuration() {
    return this.player.duration();
  }


  setCurrentTime(time) {
    this.player.currentTime(time);
    return this;
  }


  replay() {
    this.setCurrentTime(0).play();
    return this;
  }


  isPlaying() {
    return !this.player.paused();
  }

  destroy() {
    this.player.dispose();
  }
}
export default LocalVideoPlayer;
