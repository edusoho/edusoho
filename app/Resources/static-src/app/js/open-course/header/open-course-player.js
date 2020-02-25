import EsMessenger from 'app/common/messenger';
import swfobject from 'es-swfobject';
import { getSupportedPlayer } from 'common/video-player-judge';
import LocalVideoPlayer from 'app/js/player/local-video-player';
import CourseAd from './course-ad';

class OpenCoursePlayer {
  constructor({
    url,
    element,
  }) {
    this.url = url;
    this.$element = $(element);

    this.player = null;
    this.lesson = null;
    this.courseAd = null;

    this.init();
    this.initEvent();
  }

  init() {
    this.showPlayer();
  }

  initEvent() {
    this.$element.on('click', '.js-player-replay', event => this.replay(event));
    this.$element.on('click', '.js-live-video-replay-btn', event => this.onLiveVideoPlay(event));
  }

  showPlayer() {
    let self =this;
    $.get(this.url, (lesson) => {
      console.log(this.url, lesson);
      if (lesson.mediaError) {
        $('#media-error-dialog').show();
        $('#media-error-dialog').find('.modal-body .media-error').html(lesson.mediaError);
        return;
      }
      $('#media-error-dialog').hide();
      this.lesson = lesson;
      $('.js-live-video-replay-btn').removeClass('hidden');

      let mediaSourceActionsMap = {
        'iframe': this.onIframe,
        'self': this.onVideo,
      };
      let caller = mediaSourceActionsMap[lesson.mediaSource] ? mediaSourceActionsMap[lesson.mediaSource].bind(this) : undefined;

      if(lesson.mediaSource === 'NeteaseOpenCourse' && lesson.mediaUri.indexOf('.mp4') != -1){
        self._playerLocalVideo(lesson.mediaUri);
      }else if(caller === undefined && (lesson.type == 'video' || lesson.type == 'audio')) {
        caller = this.onSWF.bind(this);
      }

      if (caller === undefined) {
        return;
      }

      caller(this);

    });
  }

  onIframe() {
    let $ifrimeContent = $('#lesson-preview-iframe');
    $ifrimeContent.empty();

    var html = `<iframe class="embed-responsive-item" src="${this.lesson.mediaUri}" style="position:absolute; left:0; top:0; height:100%; width:100%; border:0px;" scrolling="no"></iframe>`;

    $ifrimeContent.html(html);
    $ifrimeContent.show();
  }

  onVideo() {
    if (getSupportedPlayer() === 'flash') {
      this.flashTip();
      return;
    }
    let lesson = this.lesson;

    if (lesson.type == 'video' || lesson.type == 'audio') {
      if (lesson.convertStatus != 'success' && lesson.storage == 'cloud') {
        $('#media-error-dialog').show();
        $('#media-error-dialog').find('.modal-body .media-error').html(Translator.trans('open_course.converting_hint'));
        return;
      }
      let playerUrl = `/open/course/${lesson.courseId}/lesson/${lesson.id}/player`;

      this.videoPlay(playerUrl);

    } else {
      return;
    }
  }

  onSWF() {
    if (!swfobject.hasFlashPlayerVersion('11')) {
      this.flashTip();
      return;
    }
    let lesson = this.lesson;
    let $swfContent = $('#lesson-preview-swf-player');

    swfobject.removeSWF('lesson-preview-swf-player');
    $swfContent.html('<div id="lesson-swf-player"></div>');
    swfobject.embedSWF(lesson.mediaUri,
      'lesson-swf-player', '100%', '100%', '9.0.0', null, null, {
        wmode: 'opaque',
        allowFullScreen: 'true'
      });
    $swfContent.show();
  }

  replay() {
    if (!this.player) {
      window.location.reload();
    } else {
      this.player.replay();
      this.courseAd.hide();
    }
  }

  onLiveVideoPlay(e) {
    this.$element.find('.js-live-header-mask').hide();

    let $target = $(e.currentTarget);

    let lesson = this.lesson;

    if (lesson.mediaError) {
      $('#media-error-dialog').show();
      $('#media-error-dialog').find('.modal-body .media-error').html(lesson.mediaError);
      return;
    }

    $('#media-error-dialog').hide();

    if (lesson.type == 'liveOpen' && lesson.replayStatus == 'videoGenerated') {
      if ((lesson.convertStatus != 'success' && lesson.storage == 'cloud')) {
        $('#media-error-dialog').show();
        $('#media-error-dialog').find('.modal-body .media-error').html(Translator.trans('open_course.converting_hint'));
        return;
      }

      let referer = $target.data('referer');
      let playerUrl = `/open/course/${lesson.courseId}/lesson/${lesson.id}/player?referer=${referer}`;

      this.videoPlay(playerUrl);

    } else {
      return;
    }
  }

  getPlayer() {
    return window.frames['viewerIframe'].window.BalloonVideoPlayer ||
           window.frames['viewerIframe'].window.player;
  }

  _playerLocalVideo(playerUrl) {
    $('#lesson-video-content').html('<video id="lesson-player" style="width: 100%;height: 100%;" class="video-js vjs-default-skin" controls preload="auto"></video>');
    new LocalVideoPlayer({
      'element' : 'lesson-player',
      'url' : playerUrl,
    });
  }

  videoPlay(playerUrl) {
    let $videoContent = $('#lesson-preview-player');
    $videoContent.html('');

    let html = `<iframe 
      class="embed-responsive-item" 
      src="${playerUrl}" 
      name="viewerIframe" 
      id="viewerIframe" 
      width="100%" 
      allowfullscreen 
      webkitallowfullscreen 
      height="100%"" 
      style="border:0px;position:absolute; left:0; top:0;"></iframe>`;

    $videoContent.html(html).show();

    let messenger = new EsMessenger({
      name: 'parent',
      project: 'PlayerProject',
      children: [document.getElementById('viewerIframe')],
      type: 'parent'
    });

    messenger.on('ready', () => {
      // @TODO 不清楚这边有什么用
      let player = this.getPlayer();
      this.player = player;
      console.log('player', player);
    });

    messenger.on('ended', () => {
      console.log('ended');
      this.onPlayEnd();
    });
  }

  onPlayEnd() {
    this.showADModal();
  }

  showADModal() {
    if (this.courseAd) {
      this.courseAd.show();
      return;
    }

    this.courseAd = new CourseAd({
      element: '#open-course-ad-modal',
      courseUrl: this.$element.data('get-recommend-course-url')
    });
    this.courseAd.show();
  }

  flashTip(flag) {
    const html = `
    <div class="alert alert-warning alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
      </button>
      ${Translator.trans('site.flash_not_install_hint')}
    </div>`;
    $('#lesson-preview-swf-player').html(html).show();
  }

}

export default OpenCoursePlayer;