import 'codeages-design';
import Qrcode from 'qrcode'
import './menu-mark-new';
import { Browser } from 'common/utils';
import notify from 'common/notify';
import Clipboard from 'clipboard';
import html2canvas from 'html2canvas'

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

const $isFreeSchool = $('#isFreeSchool');
const isChatGroupQrcodeShown = window.localStorage.getItem('isChatGroupQrcodeShown');

if (isChatGroupQrcodeShown !== '1' && $isFreeSchool && $isFreeSchool.val() === '1') {
  const qrcodeHtml = `
    <div class="modal-dialog modal-lg" >
      <div class="modal-content">
        <div class="modal-body" style="text-align: center;">
          <h2 class="modal-title" style="color: #333;font-weight: 600;margin-top: 40px">${Translator.trans('admin_v2.homepage.wechat_come')}</h2>
          <h4 class="gray-dark" style="color: #333;font-size: 20px;font-weight: 600;margin-top: 40px">${Translator.trans('admin_v2.homepage.wechat_code.m')}</h4>
          <div class="text-center">
            <img style="width:30%" src="/static-dist/app/img/admin-v2/qrcode.jpeg">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        </div>
      </div>
    </div>
  `;

  $('#modal').html(qrcodeHtml);
  $('#modal').modal('show');

  window.localStorage.setItem('isChatGroupQrcodeShown', '1');
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
  $('.js-copy-link').attr('data-clipboard-text', res.url)

  let clipboard = new Clipboard('.js-copy-link');
  clipboard.on('success', function(e) {
    notify('success', Translator.trans('admin_v2.homepage.school_info.enter.copy_success'));
  });
  
  $('.js-marketing-default-qrcode').addClass('hidden')
  $('.js-marketing-qrcode2').removeClass('hidden')
  Qrcode.toCanvas($('.js-marketing-qrcode1')[0], res.url, { width: 300, quality: 1, margin: 0 })
  Qrcode.toCanvas($('.js-marketing-qrcode2')[0], res.url, { width: 80, quality: 1, margin: 1 })

  $('.js-download-btn').on('click', () => {
    html2canvas($('.js-mall-card')[0]).then(function(canvas) {
      const link = document.createElement('a')
      link.setAttribute('download', '商城二维码')
      link.href = canvas?.toDataURL('image/png', 1)
      link.click()
    })
  })
})
