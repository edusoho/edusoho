import './jquery-lavalamp';
import { isMobileDevice } from 'common/utils';

if ($('.nav.nav-tabs').length > 0 && !isMobileDevice()) {
  $('.nav.nav-tabs').lavaLamp();
}