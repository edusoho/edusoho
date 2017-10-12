webpackJsonp(["app/js/live-course-manage/replay-lesson-modal/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	$('a[role="replay-name-span"]').click(function () {
	  var replayId = $(this).data('replayId');
	  $(this).hide();
	  $('#replay-name-input-' + replayId).show();
	});
	
	$('input[role="replay-name-input"]').blur(function () {
	  var self = $(this);
	  $(this).hide();
	  var replayId = $(this).data('replayId');
	  $('#replay-name-span-' + replayId).show();
	
	  $.post(self.data('url'), {
	    id: replayId,
	    title: self.val()
	  }, function (res) {
	    if (res) {
	      $('#replay-name-span-' + replayId).text(self.val());
	    }
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map