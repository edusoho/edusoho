webpackJsonp(["app/js/thread/event-member/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $element = $('#event-member');
	
	var memberSum = $element.data('sum');
	var currentPage = 1;
	
	$element.on('click', '.js-members-expand', function (e) {
	  var $target = $(e.currentTarget);
	
	  if ($target.data('expandAll')) {
	    $('.js-join-members').fadeIn(500);
	    $('.js-members-expand').hide();
	    $('.js-members-collapse').show();
	  } else {
	    $.get($target.data('url'), { page: currentPage + 1 }, function (result) {
	      $('.js-join-members').append(result);
	      var length = $('.js-join-members > span').length;
	
	      if (memberSum == length) {
	        $target.data('expandAll', true).hide();
	        $('.js-members-collapse').show();
	      } else {
	        currentPage = currentPage + 1;
	      }
	    });
	  }
	});
	
	$element.on('click', '.js-members-collapse', function (e) {
	  $('.js-join-members').fadeOut(500);
	  $('.js-members-expand').show();
	  $('.js-members-collapse').hide();
	});

/***/ })
]);
//# sourceMappingURL=index.js.map