import swfobject from 'es-swfobject';
import EsMessenger from 'app/common/messenger';
import LocalVideoPlayer from 'app/js/player/local-video-player';
export  default class VideoPlay {
  constructor(container) {
    this.player = {};
    this.container = container;
  }

  play() {
    if ($('#local-video-player').length) {
      this._playerLocalVideo();
    }else if ($('#swf-player').length) {
      this._playerSwf();
    } else {
      this._playVideo();
    }
  }

  _playerLocalVideo() {
    new LocalVideoPlayer({
      'url' : $('#local-video-player').data('url'),
    });
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
    let messenger = new EsMessenger({
      name: 'partner',
      project: 'PlayerProject',
      children: [],
      type: 'parent'
    });

    messenger.on('ended', function () {
      $('#task-preview-player').html($('.js-time-limit-dev').html());
    });

  }

}