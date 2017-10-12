webpackJsonp(["app/js/course-manage/question-marker/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	$('.js-switch-lesson').on('change', function () {
	  reload('');
	});
	
	$('.js-sort-btn').on('click', function () {
	  var order = 'desc';
	  var $activeIcon = $(this).find('.es-icon.active ');
	  if ($activeIcon.length > 0) {
	    $activeIcon.removeClass('active').siblings().addClass('active');
	    order = $activeIcon.siblings().data('val');
	  } else {
	    $(this).find('[data-val="desc"]').addClass('active').siblings().removeClass('active');
	  }
	  reload(order);
	});
	
	function reload(order) {
	  var url = window.location.origin + window.location.pathname + '?',
	      taskId = $('.js-switch-lesson').val();
	  window.location = url + 'taskId=' + taskId + '&order=' + order;
	}

/***/ })
]);
//# sourceMappingURL=index.js.map