webpackJsonp(["app/js/activity/flash/index"],[
/* 0 */
/***/ (function(module, exports) {

	import swfobject from "es-swfobject";
	var $el = $('#flash-player');
	
	if (!swfobject.hasFlashPlayerVersion('11')) {
	  var html = '\n    <div class="alert alert-warning alert-dismissible fade in" role="alert">\n      <button type="button" class="close" data-dismiss="alert" aria-label="Close">\n      <span aria-hidden="true">\xD7</span>\n      </button>\n      ' + Translator.trans('您的浏览器未装Flash播放器或版本太低，请先安装Flash播放器。') + '<a target="_blank" href="http://www.adobe.com/go/getflashplayer">' + Translator.trans('点击安装') + '</a>\n    </div>';
	  $el.html(html);
	  $el.show();
	} else {
	  swfobject.embedSWF($el.data('uri'), 'flash-player', '100%', '100%', "9.0.0", null, null, {
	    wmode: 'opaque',
	    allowFullScreen: 'true'
	  });
	}

/***/ })
]);