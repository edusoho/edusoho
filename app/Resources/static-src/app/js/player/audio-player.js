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
    
    if (self.options.resNo) {
      extConfig = Object.assign(extConfig, {
        resNo: self.options.resNo
      });
    }

    if (self.options.token) {
      extConfig = Object.assign(extConfig, {
        token: self.options.token
      });
    }

    if (self.options.user) {
      var user = self.options.user;
      extConfig = Object.assign(extConfig, {
        user: {
          accesskey: user.accesskey,
          globalId: user.globalId,
          id: user.id,
          name: user.name
        }
      });
    }
    const lang = (document.documentElement.lang == 'zh_CN') ? 'zh-CN' : document.documentElement.lang;
    const rememberLastPos = self.options.customPos < self.options.mediaLength;

    //范晓铖要改SDK，消除string和int的奇怪判断
    if (rememberLastPos && self.options.customPos) {
      self.options.customPos = self.options.customPos.toString();
    } else if (!self.options.customPos) {
      self.options.customPos = 0;
    } else {
      self.options.customPos = '0';
    }

    extConfig = Object.assign(extConfig, {
      id: 'lesson-player',
      playlist: self.options.url,
      audioDocMode: {
        template: self.options.content,
        sequentialMode: true,
      },
      autoplay: true, //音频自动播放开启
      initPos: self.options.customPos,
      disableModeSelection: self.options.disableModeSelection,
      rememberLastPos: rememberLastPos,
      language: lang
    });
    var player = new QiQiuYun.Player(extConfig);

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