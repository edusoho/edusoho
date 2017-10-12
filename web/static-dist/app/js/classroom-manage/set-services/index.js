webpackJsonp(["app/js/classroom-manage/set-services/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	$('.js-service-item').click(function () {
	  var $this = $(this);
	  var $input = $this.find('input');
	  if ($input.is(":checked")) {
	    $input.prop('checked', false);
	    $this.removeClass('label-primary').addClass('label-default');
	  } else {
	    $input.prop('checked', true);
	    $this.removeClass('label-default').addClass('label-primary');
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map