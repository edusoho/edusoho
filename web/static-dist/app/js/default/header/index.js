webpackJsonp(["app/js/default/header/index"],[
/* 0 */
/***/ (function(module, exports) {

	import Cookies from 'js-cookie';
	
	var PCSwitcher = $('.js-switch-pc');
	var MobileSwitcher = $('.js-switch-mobile');
	if (PCSwitcher.length) {
	  PCSwitcher.on('click', function () {
	    Cookies.set('PCVersion', 1);
	    window.location.reload();
	  });
	}
	if (MobileSwitcher.length) {
	  MobileSwitcher.on('click', function () {
	    Cookies.remove('PCVersion');
	    window.location.reload();
	  });
	}
	
	$('.js-back').click(function () {
	  if (history.length !== 1) {
	    history.go(-1);
	  } else {
	    location.href = '/';
	  }
	});

/***/ })
]);