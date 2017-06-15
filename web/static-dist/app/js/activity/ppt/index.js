webpackJsonp(["app/js/activity/ppt/index"],[
/* 0 */
/***/ (function(module, exports) {

	import PptPlayer from '../../../common/ppt-player';
	import ActivityEmitter from "../activity-emitter";
	
	var emitter = new ActivityEmitter();
	var $content = $('#activity-ppt-content');
	var watermarkUrl = $content.data('watermarkUrl');
	
	var createPPT = function createPPT(watermark) {
	  var ppt = new PptPlayer({
	    element: '#activity-ppt-content',
	    slides: $content.data('slides').split(','),
	    watermark: watermark
	  });
	
	  if ($content.data('finishType') === 'end') {
	    ppt.once('end', function (data) {
	      emitter.emit('finish', data);
	    });
	  }
	
	  return ppt;
	};
	
	if (watermarkUrl === undefined) {
	  var ppt = createPPT();
	} else {
	  $.get(watermarkUrl).then(function (watermark) {
	    var ppt = createPPT(watermark);
	  }).fail(function (error) {
	    console.error(error);
	  });
	}

/***/ })
]);