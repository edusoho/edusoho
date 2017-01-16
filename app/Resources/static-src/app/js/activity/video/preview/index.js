import swfobject from 'es-swfobject';
import EsMessenger from '../../../../common/messenger';
class VideoPlay {
  constructor(container) {
    this.player = {};
    this.container = container;
  }

  play() {
    if ($('#swf-player').length) {
      this._playerSwf();
    } else {
      this._playVideo();
    }
  }

  _playerSwf() {
    const swf_dom = 'swf-player';
    swfobject.embedSWF($('#' + swf_dom).data('url'),
      swf_dom, '100%', '100%', "9.0.0", null, null, {
        wmode: 'opaque',
        allowFullScreen: 'true'
      });
  }

  _playVideo() {
    var messenger = new EsMessenger({
      name: 'parent',
      project: 'PlayerProject',
      children: [],
      type: 'parent'
    });

    if ($(this.container).data('timelimit')) {
      $(".modal-footer").prepend($('.js-buy-text').html());
      messenger.on("ended", function () {
        $('#task-preview-player').html($('.js-time-limit-dev').html());
      });
    }

  }

}
let videoplay = new VideoPlay('#lesson-video-content');
videoplay.play();