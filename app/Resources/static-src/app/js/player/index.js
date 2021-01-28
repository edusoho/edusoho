import PlayerFactory from './player-factory';
import EsMessenger from '../../common/messenger';
import DurationStorage from '../../common/duration-storage';

class Show {

  constructor(element) {
    let container = $(element);
    this.htmlDom = $(element);
    this.content = container.data('content');
    this.userId = container.data('userId');
    this.userName = container.data('userName');
    this.fileId = container.data('fileId');
    //用于定位播放资源
    this.fileGlobalId = container.data('fileGlobalId');
    this.courseId = container.data('courseId');
    this.lessonId = container.data('lessonId');
    this.timelimit = container.data('timelimit');
    //用于鉴权
    this.token = container.data('token');
    this.fileType = container.data('fileType');
    this.fileLength = container.data('fileLength');
    this.enablePlaybackRates = container.data('enablePlaybackRates');
    this.videoH5 = container.data('videoH5');
    this.watermark = container.data('watermark');
    this.accesskey = container.data('accessKey');
    this.fingerprint = container.data('fingerprint');
    this.fingerprintSrc = container.data('fingerprintSrc');
    this.fingerprintTime = container.data('fingerprintTime');
    this.jsPlayer = container.data('jsPlayer');
    this.markerUrl = container.data('markerurl');
    this.finishQuestionMarkerUrl = container.data('finishQuestionMarkerUrl');
    this.starttime = container.data('starttime');
    this.agentInWhiteList = container.data('agentInWhiteList');
    this.disableVolumeButton = container.data('disableVolumeButton');
    this.disablePlaybackButton = container.data('disablePlaybackButton');
    this.disableModeSelection = container.data('disableModeSelection');
    this.disableResolutionSwitcher = container.data('disableResolutionSwitcher');
    this.subtitles = container.data('subtitles');
    this.autoplay = container.data('autoplay');
    this.rememberLastPos = container.data('rememberLastPos');
    let $iframe = $(window.parent.document.getElementById('task-content-iframe'));
    if ($iframe.length > 0 && parseInt($iframe.data('lastLearnTime')) != parseInt(DurationStorage.get(this.userId, this.fileId))) {
      DurationStorage.del(this.userId, this.fileId);
      DurationStorage.set(this.userId, this.fileId, $iframe.data('lastLearnTime'));
    }
    this.lastLearnTime = DurationStorage.get(this.userId, this.fileId);
    this.strictMode = container.data('strict');
    this.url = container.data('url');
    this.fileStorage = container.data('fileStorage');
    this.initView();
    this.initEvent();
  }

  initView() {
    let html = '';
    if (this.fileType == 'video') {
      html += '<div id="lesson-player" style="width: 100%;height: 100%;"></div>';
    } else if (this.fileType == 'audio') {
      html += '<div id="lesson-player" style="width: 100%;height: 100%;" class="video-js vjs-default-skin" controls preload="auto"></audio>';
    }
    this.htmlDom.html(html);
    this.htmlDom.show();
  }

  initPlayer() {
    const customPos = parseInt(this.lastLearnTime) ? parseInt(this.lastLearnTime) : 0;
    let options = {
      element: '#lesson-player',
      content: this.content,
      mediaType: this.fileType,
      fingerprint: this.fingerprint,
      fingerprintSrc: this.fingerprintSrc,
      fingerprintTime: this.fingerprintTime,
      watermark: this.watermark,
      starttime: this.starttime,
      agentInWhiteList: this.agentInWhiteList,
      timelimit: this.timelimit,
      enablePlaybackRates: this.enablePlaybackRates,
      disableModeSelection: this.disableModeSelection,
      videoH5: this.videoH5,
      controlBar: {
        disableVolumeButton: this.disableVolumeButton,
        disablePlaybackButton: this.disablePlaybackButton,
        disableResolutionSwitcher: this.disableResolutionSwitcher
      },
      //用户以及网校信息
      user: {
        accesskey: this.accesskey,
        globalId: this.fileGlobalId,
        id: this.userId,
        name: this.userName
      },
      textTrack: this.transToTextrack(this.subtitles),
      autoplay: this.autoplay,
      customPos: customPos,
      mediaLength: this.fileLength,
      strictMode: this.strictMode,
      rememberLastPos: this.rememberLastPos
    };
    if (this.fileStorage === 'local') {
      options = Object.assign(options, {
        url: this.url,
      });
    } else {
      //文件存储类型：cloud、supplier供应商
      options = Object.assign(options, {
        resNo: this.fileGlobalId,
        token: this.token,
      });
    }
    return window.player = PlayerFactory.create(
      this.jsPlayer, options
    );
  }
  transToTextrack(subtitles) {
    let textTracks = [];
    if (subtitles) {
      for (let i in subtitles) {
        let item = {
          label: subtitles[i].name,
          src: subtitles[i].url,
          'default': ('default' in subtitles[i]) ? subtitles[i]['default'] : false
        };
        textTracks.push(item);
      }
    }

    // set first item to default if no default
    for (let i in textTracks) {
      if (textTracks[i]['default']) {
        return;
      }
      textTracks[0]['default'] = true;
    }
    return textTracks;
  }

  initMesseger() {
    return new EsMessenger({
      name: 'parent',
      project: 'PlayerProject',
      type: 'child'
    });
  }

  isCloudVideoPalyer() {
    return 'balloon-cloud-video-player' == this.jsPlayer;
  }

  isCloudAudioPlayer() {
    return 'audio-player' == this.jsPlayer;
  }

  initEvent() {
    let player = this.initPlayer();
    let messenger = this.initMesseger();
    player.on('ready', () => {
      messenger.sendToParent('ready', {
        pause: true,
        currentTime: player.getCurrentTime()
      });
      if (!this.isCloudVideoPalyer() && !this.isCloudAudioPlayer()) {
        let time = DurationStorage.get(this.userId, this.fileId);
        if (time > 0) {
          player.setCurrentTime(time);
        }
        player.play();
      }

      if (this.isCloudVideoPalyer()) {
        if (this.markerUrl) {
          $.getJSON(this.markerUrl, function(questions) {
            player.setQuestions(questions);
          });
        }
      }
    });

    player.on('answered', (data) => {
      let regExp = /course\/(\d+)\/task\/(\d+)\//;
      let matches = regExp.exec(window.location.href);

      if (matches) {
        $.post(this.finishQuestionMarkerUrl, {
          'questionMarkerId': data.questionMarkerId,
          'answer': data.userAnswers,
          'type': data.type,
          'courseId': matches[1],
          'taskId': matches[2],
        }, function(result) {

        });
      }

    });

    player.on('timechange', (data) => {
      messenger.sendToParent('timechange', {
        pause: true,
        currentTime: player.getCurrentTime()
      });
      if (!this.isCloudVideoPalyer() && !this.isCloudAudioPlayer()) {
        if (parseInt(player.getCurrentTime()) != parseInt(player.getDuration())) {
          DurationStorage.del(this.userId, this.fileId);
          DurationStorage.set(this.userId, this.fileId, player.getCurrentTime());
        }
      } else {
        DurationStorage.del(this.userId, this.fileId);
        DurationStorage.set(this.userId, this.fileId, player.getCurrentTime());
      }
    });

    player.on('paused', () => {
      messenger.sendToParent('paused', {
        pause: true,
        currentTime: player.getCurrentTime()
      });
    });

    player.on('playing', () => {
      messenger.sendToParent('playing', {
        pause: false,
        currentTime: player.getCurrentTime()
      });
    });

    player.on('ended', (msg) => {
      messenger.sendToParent('ended', {
        stop: true,
        playerMsg: msg
      });
      if (!this.isCloudVideoPalyer() && !this.isCloudAudioPlayer()) {
        DurationStorage.del(this.userId, this.fileId);
      }
    });
  }


}
new Show('#lesson-video-content');
