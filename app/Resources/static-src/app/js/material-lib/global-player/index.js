import swfobject from 'es-swfobject';
import { getSupportedPlayer } from 'common/video-player-judge';
let $element = $('#global-player');
import EsMessenger from 'app/common/messenger';

const videoPlayer = () => {
  let play = new QiQiuYun.Player({
    id: 'global-player',
    playServer: app.cloudPlayServer,
    resNo: $element.data('resNo'),
    token: $element.data('token'),
    user: {
      id: $element.data('userId'),
      name: $element.data('userName')
    }
  });
  
  let messenger = new EsMessenger({
    name: 'parent',
    project: 'PlayerProject',
    type: 'child'
  });
  
  play.on('video.timeupdate', (mes) => {
    messenger.sendToParent('video.timeupdate', mes);
  });  
};

const flashTip = (flag) => {
  const $tip = $('.js-flash-tip');
  if (!swfobject.hasFlashPlayerVersion('11')) {
    const html = `
    <div class="alert alert-warning alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
      </button>
      ${Translator.trans('site.flash_not_install_hint_1')}
    </div>`;
    $tip.html(html);
    const $cloudVideo = $('.js-video-wrap');
    if ($cloudVideo.length) {
      $cloudVideo.addClass('hidden');
    }
  } else {
    $tip.html('');
    videoPlayer();
  }
};


alert(getSupportedPlayer());

if (getSupportedPlayer() === 'flash') {

  flashTip();
} else {
  videoPlayer();
}

