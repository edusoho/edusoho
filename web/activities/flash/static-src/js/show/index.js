import swfobject from 'es-swfobject';
let $el = $('#flash-player');

if (!swfobject.hasFlashPlayerVersion('11')) {
  let html = `
    <div class="alert alert-warning alert-dismissible fade in" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
      </button>
      ${Translator.trans('site.flash_not_install_hint')}
    </div>`;
  $el.html(html);
  $el.show();
} else {
  swfobject.embedSWF($el.data('uri'),
    'flash-player', '100%', '100%', '9.0.0', null, null, {
      wmode: 'opaque',
      allowFullScreen: 'true'
    });
}
