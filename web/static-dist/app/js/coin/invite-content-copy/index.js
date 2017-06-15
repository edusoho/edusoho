webpackJsonp(["app/js/coin/invite-content-copy/index"],[
/* 0 */
/***/ (function(module, exports) {

	import notify from 'common/notify';
	
	$('#copy').click(function () {
	  $("#content").select();
	  document.execCommand("Copy");
	  notify('success', Translator.trans('链接复制成功'));
	});

/***/ })
]);