import 'codeages-design';
import Qrcode from 'qrcode'
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
  });
});
let csrfToken = document.getElementsByTagName('meta')['csrf-token'];
if (csrfToken) {
  localStorage.setItem('csrf-token', csrfToken.content);
}

$.ajax({
  url: '/api/mall_info',
  headers: {
    Accept: 'application/vnd.edusoho.v2+json'
  }
}).then(res => {
  if (res.isShow) {
    $('.js-sass').toggleClass('hidden')
  }

  if (!res.isInit) {
    $('.js-marketing').toggleClass('hidden')
    $('.js-share-container').addClass('info-disable')
  }

  $('.js-marketing-url').text(res.url)

  Qrcode.toDataURL(url).then(imgUrl => {
    $('.js-marketing-qrcode').attr('src', imgUrl)
  })
})
