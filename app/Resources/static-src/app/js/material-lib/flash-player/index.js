import swfobject from 'es-swfobject';

let $player = $('#flash-player');

if (!swfobject.hasFlashPlayerVersion('11')) {
  let html = `
    <div class="alert alert-warning alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
      </button>
     您的浏览器未装Flash播放器或者该flash版本低于11或者flash被禁用，请检查浏览器中的flash播放器能否正常使用。
    </div>`;
  $player.html(html).show();
  
} else {
  let params = $player.data('params');
  swfobject.embedSWF(params.url,
    'flash-player', '100%', '100%', '9.0.0', null, null, {
      wmode: 'opaque',
      allowFullScreen: 'true'
    });

}