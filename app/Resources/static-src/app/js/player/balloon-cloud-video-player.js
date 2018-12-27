import Emitter from 'component-emitter';
class BalloonCloudVideoPlayer extends Emitter {

  constructor(options) {
    super();
    this.options = options;
    this.player = {};
    this.setup();
  }

  setup() {
    let element = this.options.element;

    var self = this;

    let extConfig = {};

    //字幕
    if (self.options.textTrack.length) {
      extConfig = Object.assign(extConfig, {
        textTrack: self.options.textTrack
      });
    }

    if (self.options.watermark) {
      extConfig = Object.assign(extConfig, {
        watermark: {
          file: self.options.watermark,
          pos: 'top.right', //top.right, bottom.right, bottom.left, center
          xrepeat: 0,
          opacity: 0.5
        }
      });
    }

    if (self.options.fingerprint) {
      extConfig = Object.assign(extConfig, {
        fingerprint: {
          html: self.options.fingerprint,
          duration: self.options.fingerprintTime
        }
      });
    }

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

    const remeberLastPos = self.options.customPos ? true : false;
    self.options.customPos = self.options.customPos.toString();

    extConfig = Object.assign(extConfig, {
      id: $(self.options.element).attr('id'),
      disableControlBar: self.options.disableControlBar,
      disableProgressBar: self.options.disableProgressBar,
      playlist: self.options.url,
      remeberLastPos: remeberLastPos,
      customPos: self.options.customPos,
      videoHeaderLength: self.options.videoHeaderLength,
      autoplay: self.options.autoplay
    });
    var player = new VideoPlayerSDK(extConfig);

    player.on('ready', function(e) {
      self.emit('ready', e);
    });

    player.on('timeupdate', function(e) {
      //    player.__events get all the event;
      self.emit('timechange', e);
    });

    player.on('firstplay', function (e) {
      player.setCurrentTime(self.options.customPos);
    });

    player.on('ended', function(e) {
      self.emit('ended', e);
    });

    player.on('playing', function(e) {
      self.emit('playing', e);
    });

    player.on('paused', function(e) {
      self.emit('paused', e);
    });

    player.on('exam.answered', function(e) {
      var data = e.data;
      data['type'] = self.convertQuestionType(data.type, 'cloud');
      self.emit('answered', data);
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

  setQuestions(questions) {

    if (questions.length > 0) {

      for (var i in questions) {
        questions[i]['type'] = this.convertQuestionType(questions[i].type, 'es');
      }

      var exam = {
        popupExam: {
          config: {
            'mode': 'middle'
          },
          questions: questions
        }
      };

      this.player.setExams(exam);
    }

    return this;
  }

  convertQuestionType(source, from) {
    var map = [ //云播放器弹题的字段值跟ES不太一致
      {
        es: 'choice',
        cloud: 'multiChoice'
      }, {
        es: 'single_choice',
        cloud: 'choice'
      }, {
        es: 'determine',
        cloud: 'judge'
      }, {
        es: 'fill',
        cloud: 'completion'
      }, {
        es: 'uncertain_choice',
        cloud: 'uncertainChoice'
      }
    ];

    for (var i in map) {
      if (from == 'es' && map[i]['es'] == source) {
        return map[i]['cloud'];
      }
      if (from == 'cloud' && map[i]['cloud'] == source) {
        return map[i]['es'];
      }
    }

    return source;
  }

}

export default BalloonCloudVideoPlayer;