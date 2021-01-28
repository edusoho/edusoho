import notify from 'common/notify';
import Clipboard from 'clipboard';

let clipboard = new Clipboard('.js-copy-link');

clipboard.on('success', function(e) {
  notify('success', Translator.trans('coin.invite_url_copy_success_hint'));
});


