import 'codeages-design';
import './menu-mark-new';
import { Browser } from 'common/utils';

if (Browser.ie || Browser.ie11 || Browser.edge) {
  $('body').addClass('admin-ie-body');
}

$('.js-safari-modal-click').on('click', function (e) {
  cd.modal({
    el: '#cd-modal',
    ajax: false,
    url: '',
    maskClosable: true,
  }).on('ok', ($modal, modal) => {
    modal.trigger('close');
  })
})