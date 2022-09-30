import 'codeages-design';
import Qrcode from 'qrcode'
import './menu-mark-new';
import { Browser } from 'common/utils';
import notify from 'common/notify';
import Clipboard from 'clipboard';

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

    return;
  }

  $('.js-marketing-url').text(res.url)
  $('.js-copy-link').prop('data-clipboard-text', res.url)

  let clipboard = new Clipboard('.js-copy-link');
  clipboard.on('success', function(e) {
    notify('success', Translator.trans('admin_v2.homepage.school_info.enter.copy_success'));
  });
  
  Qrcode.toCanvas($('.js-marketing-qrcode')[0], res.url, { width: 80, quality: 1, margin: 1 })

  $('.js-download-btn').on('click', () => {
    const link = document.createElement('a')
    const canvas = $('.js-marketing-qrcode')[0]
    link.setAttribute('download', 'qrcode')
    link.href = canvas?.toDataURL('image/png', 1)
    link.click()
  })
})
