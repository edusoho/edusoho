import Cookies from 'js-cookie';
import ajax from '../../../../common/api/ajax';

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

  $.ajax({
    type : "post",
    url : $this.data('url'),
    beforeSend() {
      $('.tab-pane.active').find('.js-inform-loading').removeClass('hidden');
    },
  }).done(() => {
    $('.js-inform-loading').addClass('hidden');
  });
})

ajax({
  url: '/api/newNotifications',
  type: 'GET',
  beforeSend() {
    $('.tab-pane.active').find('.js-inform-loading').removeClass('hidden');
  },
}).then((result) => {
  $('.js-inform-loading').addClass('hidden');
  $('.js-inform-notification').append(result);
}, () => {
});

$('.js-user-nav-dropdown').on('click', '.js-inform-notification', (event) => {
  const $item = $(event.currentTarget);
  window.open($item.data('url'));
})

