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

$('body').on('click', '.js-user-nav-dropdown', function (event) {
  event.stopPropagation();
});


$('.js-inform-tab').click(function(e) {
  const $this = $(this);
  e.preventDefault();
  $this.addClass('active').siblings().removeClass('active');
  $this.tab('show');
  var id = $this[0].id;
  if (id == 'conversation') {
    Api.conversation.search({
      beforeSend() {
        $('.tab-pane.active').find('.js-inform-loading').removeClass('hidden');
      },
     }).then((res) => {
      $('.js-inform-conversation').append(res);
      // $('.js-inform-loading').addClass('hidden');
     }).catch((res) => {
      // 异常捕获
      // console.log('catch', res.responseJSON.error.message);
    })
  }
  if (id == 'newNotification') {
    Api.newNotification.search({
      beforeSend() {
        $('.tab-pane.active').find('.js-inform-loading').removeClass('hidden');
      },
     }).then((res) => {
      $('.js-inform-newNotification').empty();
      $('.js-inform-newNotification').append(res);
      // $('.js-inform-loading').addClass('hidden');
     }).catch((res) => {
      // 异常捕获
      // console.log('catch', res.responseJSON.message);
    })
  }
})

Api.newNotification.search({
  beforeSend() {
    $('.tab-pane.active').find('.js-inform-loading').removeClass('hidden');
  },
 }).then((res) => {
  $('.js-inform-newNotification').append(res);
  $('.js-inform-loading').addClass('hidden');
 }).catch((res) => {
  // 异常捕获
  // console.log('catch', res.responseJSON.message);
})

$('.js-user-nav-dropdown').on('click', '.js-inform-notification', (event) => {
  const $item = $(event.currentTarget);
  window.open($item.data('url'));
})

