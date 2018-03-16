import './jquery-lavalamp';
import { isMobileDevice } from 'common/utils';

if ($('.nav.nav-tabs').length > 0 && !isMobileDevice()) {
  // console.log(lavaLamp);
  $('.nav.nav-tabs').lavaLamp();
}