webpackJsonp(["app/js/course-manage/live-replay/index"],[
/* 0 */
/***/ (function(module, exports) {

	import notify from 'common/notify';
	
	$('.js-generate-replay').on('click', function (event) {
	  var $this = $(event.currentTarget);
	  var url = $this.data('url');
	  if (!url) return;
	  Promise.resolve($.post(url)).then(function (success) {
	    notify('success', '生成录制回放成功');
	    window.location.reload();
	  }).catch(function (response) {
	    var error = JSON.parse(response.responseText);
	    var code = error.code;
	    var message = error.error;
	    notify('danger', '发生了异常，请稍后重试');
	  });
	});

/***/ })
]);