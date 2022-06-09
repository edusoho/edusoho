import Emitter from 'component-emitter';
import postal from 'postal';
import 'postal.federation';
import 'postal.xframe';
import screenfull from 'es-screenfull';

class BalloonCloudVideoPlayer extends Emitter {

  constructor(options) {
    super();
    this.options = options;
    this.player = {};
    this.setup();
  }

  setup() {
    const resultStatus = $('[name="task-result-status"]', window.parent.document).val();
    const mode = $('[name="mode"]', window.parent.document).val();
    const activityFinishType = $('#video-content').data('finishType');
    const activityFinishData = $('#video-content').data('finishData');
    const disableProgressBar = ((resultStatus === 'start' || resultStatus === 'none') && 'learn' === mode && activityFinishType === 'end' && activityFinishData);
    const disableSeek = disableProgressBar ? 'forward' : 'none';

    console.log(disableSeek);
    let element = this.options.element;

    var self = this;

    let extConfig = {};

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

    if (!disableProgressBar && self.options.enablePlaybackRates) {
      extConfig = Object.assign(extConfig, {
        playbackRates: ['0.75', '1.0', '1.25', '1.5', '2.0', '3.0']
      });
    }

    if (self.options.videoH5) {
      extConfig = Object.assign(extConfig, {
        h5: true
      });
    }

    if (self.options.playerType) {
      extConfig = Object.assign(extConfig, {
        playerType: self.options.playerType
      });
    }

    if (self.options.controlBar) {
      extConfig = Object.assign(extConfig, {
        controlBar: self.options.controlBar
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

    const rememberLastPos = (self.options.customPos && self.options.rememberLastPos) ? true : false;

    const lang = (document.documentElement.lang == 'zh_CN') ? 'zh-CN' : document.documentElement.lang;
    self.options.customPos = self.options.customPos.toString();
    extConfig = Object.assign(extConfig, {
      id: $(self.options.element).attr('id'),
      sdkBaseUri: app.cloudSdkBaseUri,
      disableDataUpload: app.cloudDisableLogReport,
      disableSentry: app.cloudDisableLogReport,
      disableControlBar: self.options.disableControlBar,
      disableProgressBar: self.options.disableProgressBar,
      disableFullscreen: self.options.disableFullscreen,
      disableSeek: disableSeek,
      playlist: self.options.url,
      rememberLastPos: rememberLastPos,
      initPos: self.options.customPos,
      autoplay: self.options.autoplay,
      strictMode: !self.options.strictMode,
      language: lang
    });
    console.log(extConfig);
    var player = new QiQiuYun.Player(extConfig);
    player.on('ready', function(e) {
      self.emit('ready', e);
    });

    player.on('timeupdate', function(e) {
      //    player.__events get all the event;
      self.currentTime = e.currentTime;
      self.duration = e.duration;
      self.emit('timechange', e);
    });

    player.on('firstplay', function (e) {
      player.setCurrentTime(self.options.customPos);
    });

    player.on('unableConfirm', function (e) {
      $('.js-back-link', parent.document)[0].click();
    });

    player.on('ended', function(e) {
      let message = {
        'mode' : self.playMode,
        'currentTime': self.currentTime,
        'duration': self.duration,
      };
      self.emit('ended', message);
    });

    player.on('playing', function(e) {
      self.emit('playing', e);
    });

    player.on('paused', function(e) {
      self.emit('paused', e);
    });

    player.on('exam.answered', function(data) {
      data['type'] = self.convertQuestionType(data.type, 'cloud');
      self.emit('answered', data);
    });

    player.on('requestFullscreen', function(data) {
      self.emit('requestFullscreen', data);
    })

    this.player = player;
    this._registerChannel();
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

  _registerChannel() {
    postal.instanceId('task');

    postal.fedx.addFilter([
      {
        channel: 'task-events', //接收 activity iframe的事件
        topic: 'monitoringEvent',
        direction: 'in'
      }
    ]);

    postal.subscribe({
      channel: 'task-events',
      topic: 'monitoringEvent',
      callback: (type) => {
        if (screenfull.isFullscreen) {
          screenfull.exit();
        }
        if (type === 'pause') {
          this.pause();
        } else if (type === 'play') {
          this.play();
        }
      }
    });

    return this;
  }

}

export default BalloonCloudVideoPlayer;