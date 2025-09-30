import swfobject from 'es-swfobject';
import { getSupportedPlayer } from 'common/video-player-judge';
import EsMessenger from 'app/common/messenger';
const $element = $('#global-player');

const globalPlayer = () => {
  const play = new QiQiuYun.Player({
    id: 'global-player',
    // playServer: app.cloudPlayServer,
    sdkBaseUri: app.cloudSdkBaseUri,
    disableDataUpload: app.cloudDisableLogReport,
    disableSentry: app.cloudDisableLogReport,
    resNo: $element.data('resNo'),
    token: $element.data('token'),
    user: {
      id: $element.data('userId'),
      name: $element.data('userName')
    },
    playbackRates: $element.data('enablePlaybackRates') ? ['0.5', '1.0', '1.25', '1.5', '2.0'] : 0,
  });
  const messenger = new EsMessenger({
    name: 'parent',
    project: 'PlayerProject',
    type: 'child'
  });

  play.on('timeupdate', (mes) => {
    messenger.sendToParent('video.timeupdate', mes);
  });  
};

const flashTip = () => {
  if (!swfobject.hasFlashPlayerVersion('11')) {
    const html = `
    <div class="alert alert-warning alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
      </button>
      ${Translator.trans('site.flash_not_install_hint')}
    </div>`;
    $element.html(html).show();
  } else {
    globalPlayer();
  }
};

const init = () => {
  if ($element.data('fileType') === 'video' && getSupportedPlayer() === 'flash') {
    flashTip();
  } else {
    globalPlayer();
  }
};

init();