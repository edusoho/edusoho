import Emitter from 'component-emitter';
class AudioPlayer extends Emitter {

  constructor(options) {
    super();
    this.options = options;
    this.player = {};
    this.setup();
  }

  setup() {
    let element = this.options.element;
    console.log(this.options);

    var self = this;

    let extConfig = {};

    if (self.options.timelimit) {
      extConfig = Object.assign(extConfig, {
        pluck: {
          timelimit: self.options.timelimit,
          text: Translator.trans('activity.video.try_watch_finish_hint'),
          display: true
        }
      });
    }

    if (self.options.enablePlaybackRates) {
      extConfig = Object.assign(extConfig, {
        playbackRates: ['0.8', '1.0', '1.25', '1.5', '2.0']
      });
    }

    if (self.options.videoH5) {
      extConfig = Object.assign(extConfig, {
        h5: true
      });
    }

    if (self.options.controlBar) {
      extConfig = Object.assign(extConfig, {
        controlBar: self.options.controlBar
      });
    }

    if (self.options.statsInfo) {
      var statsInfo = self.options.statsInfo;
      extConfig = Object.assign(extConfig, {
        statsInfo: {
          accesskey: statsInfo.accesskey,
          globalId: statsInfo.globalId,
          userId: statsInfo.userId,
          userName: statsInfo.userName
        }
      });
    }

    extConfig = Object.assign(extConfig, {
      id: 'lesson-player',
      disableControlBar: self.options.disableControlBar,
      disableProgressBar: self.options.disableProgressBar,
      playlist: self.options.url,
      template: self.options.content,
      // remeberLastPos: true,
    //   videoHeaderLength: self.options.videoHeaderLength,
      // autoplay: self.options.autoplay
    });
    var player = new AudioPlayerSDK(extConfig);

    player.on('ready', function(e) {
      self.emit('ready', e);
    });

    // player.on('timeupdate', function(e) {
    //   //    player.__events get all the event;
    //   self.emit('timechange', e);
    // });

    player.on('ended', function(e) {
      self.emit('ended', e);
    });

    player.on('playing', function(e) {
      self.emit('playing', e);
    });

    player.on('paused', function(e) {
      self.emit('paused', e);
    });

    this.player = player;
  }

  play() {
    this.player.play();
  }

  pause() {
    this.player.pause();
  }

  getCurrentTime() {
    return this.player.getCurrentTime();
  }

  setCurrentTime(time) {
    this.player.setCurrentTime(time);
    return this;
  }

  replay() {
    this.setCurrentTime(0).play();
    return this;
  }

  isPlaying() {
    if (this.player && this.player.paused) {
      return !this.player.paused();
    }
    return false;
  }

}

export default AudioPlayer;