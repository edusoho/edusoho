import 'codeages-design';
import './menu-mark-new';
import { Browser } from 'common/utils';

if (Browser.ie || Browser.ie11 || Browser.edge) {
  $('body').addClass('admin-ie-body');
}