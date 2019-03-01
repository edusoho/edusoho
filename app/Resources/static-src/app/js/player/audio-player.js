import Emitter from 'component-emitter';
class AudioPlayer extends Emitter {

  constructor(options) {
    super();
    this.options = options;
    this.playMode = 'sequence'; //默认开启
    this.player = {};
    this.setup();
  }

  setup() {
    let element = this.options.element;

    var self = this;

    let extConfig = {};

    if (self.options.enablePlaybackRates) {
      extConfig = Object.assign(extConfig, {
        playbackRates: ['0.8', '1.0', '1.25', '1.5', '2.0']
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
    
    const remeberLastPos = self.options.customPos < self.options.mediaLength;

    //范晓铖要改SDK，消除string和int的奇怪判断
    if (remeberLastPos && self.options.customPos) {
      self.options.customPos = self.options.customPos.toString();
    } else if (!self.options.customPos) {
      self.options.customPos = 0;
    } else {
      self.options.customPos = '0';
    }

    extConfig = Object.assign(extConfig, {
      id: 'lesson-player',
      playlist: self.options.url,
      template: self.options.content,
      autoplay: true, //音频自动播放开启
      customPos: self.options.customPos,
      disableModeSelection: self.options.disableModeSelection,
      remeberLastPos: remeberLastPos,
      sequentialMode: true,
    });
    var player = new AudioPlayerSDK(extConfig);

    player.on('ready', function(e) {
      self.emit('ready', e);
    });

    player.on('firstplay', function (e) {
      player.setCurrentTime(self.options.customPos);
    });

    player.on('timeupdate', function(e) {
      //    player.__events get all the event;
      self.emit('timechange', e);
    });

    player.on('modeChanged', function (e) {
      self.playMode = e.data.mode;
    });

    player.on('ended', function(e) {
      let message = {
        'mode' : self.playMode
      };
      console.log(message);
      self.emit('ended', message);
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