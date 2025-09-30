import './jquery-lavalamp';
import { isMobileDevice } from 'common/utils';

if ($('.nav.nav-tabs').length > 0 && !isMobileDevice()) {
  if ($('meta[name=lavalamp-enable]').attr('content') != 0) {
    $('.nav.nav-tabs').lavaLamp();
  }
}