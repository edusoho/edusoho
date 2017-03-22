const Cookie = require('cookie');

const PCSwitcher = $('.js-switch-pc');
const MobileSwitcher = $('.js-switch-mobile');
if (PCSwitcher.length) {
  PCSwitcher.on('click', () => {
    Cookie.set('PCVersion', 1);
    window.location.reload();
  });
}
if (MobileSwitcher.length) {
  MobileSwitcher.on('click', () => {
    Cookie.remove('PCVersion');
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
