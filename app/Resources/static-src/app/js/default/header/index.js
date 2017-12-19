import Cookies from 'js-cookie';
import notify from 'common/notify';
import Api from 'common/api';

const PCSwitcher = $('.js-switch-pc');
const MobileSwitcher = $('.js-switch-mobile');
if (PCSwitcher.length) {
  PCSwitcher.on('click', () => {
    Cookies.set('PCVersion', 1);
    window.location.reload();
  });
}
if (MobileSwitcher.length) {
  MobileSwitcher.on('click', () => {
    Cookies.remove('PCVersion');
    window.location.reload();
  });
}


$('.js-back').click(() => {
  if (history.length !== 1) {
    history.go(-1);
  } else {
    location.href = '/';
  }
});

$('body').on('click', '.js-user-nav-dropdown', (event) => {
  event.stopPropagation();
});


$('.js-inform-tab').click(function(e) {
  const $this = $(this);
  e.preventDefault();
  $this.addClass('active').siblings().removeClass('active');
  $this.tab('show');
  const id = $this[0].id;
  const isEmpty = $('.tab-pane.active').find('.js-inform-empty').length;
  if (id === 'conversation' && !isEmpty) {
    Api.conversation.search().then((res) => {
      $('.tab-pane.active').find('.js-inform-loading').addClass('hidden');
      $('.js-inform-conversation').empty();
      $('.js-inform-conversation').append(res);
     }).catch((res) => {
      // 异常捕获
      console.log('catch', res.responseJSON.error.message);
    })
  }
  if (id === 'newNotification' && !isEmpty) {
    Api.newNotification.search().then((res) => {
      $('.tab-pane.active').find('.js-inform-loading').addClass('hidden');
      $('.js-inform-newNotification').empty();
      $('.js-inform-newNotification').append(res);
     }).catch((res) => {
      // 异常捕获
      console.log('catch', res.responseJSON.error.message);
    })
  }
})

$(document).ajaxSend(() => {
  // 加载loading效果
  const $dom = $('.js-inform-loading');
  const loading = cd.loading();
  $dom.removeClass('hidden');
  $dom.html(loading);
});

Api.newNotification.search().then((res) => {
  $('.tab-pane.active').find('.js-inform-loading').addClass('hidden');
  $('.js-inform-newNotification').append(res);
 }).catch((res) => {
  // 异常捕获
  console.log('catch', res.responseJSON.error.message);
})


$('.js-user-nav-dropdown').on('click', '.js-inform-notification', (event) => {
  const $item = $(event.currentTarget);
  window.open($item.data('url'));
})
