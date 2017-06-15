webpackJsonp(["app/js/activity/doc/index"],[
/* 0 */
/***/ (function(module, exports) {

	import DocPlayer from '../../../common/doc-player';
	
	var $element = $('#document-content');
	var watermarkUrl = $element.data('watermark-url');
	
	if (watermarkUrl) {
	  $.get(watermarkUrl, function (watermark) {
	    console.log(watermark);
	    initDocPlayer(watermark);
	  });
	} else {
	  initDocPlayer('');
	}
	
	function initDocPlayer(contents) {
	  var doc = new DocPlayer({
	    element: $element,
	    swfUrl: $element.data('swf'),
	    pdfUrl: $element.data('pdf'),
	    watermarkOptions: {
	      contents: contents,
	      xPosition: 'center',
	      yPosition: 'center',
	      rotate: 45
	    },
	    canCopy: $element.data('disableCopy')
	  });
	}

/***/ })
]);