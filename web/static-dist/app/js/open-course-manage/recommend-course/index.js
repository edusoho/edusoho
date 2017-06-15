webpackJsonp(["app/js/open-course-manage/recommend-course/index"],[
/* 0 */
/***/ (function(module, exports) {

	import sortList from 'common/sortable';
	
	$(".course-list-group").on('click', '.close', function () {
	  var recommendId = $(this).data('recommendId');
	  var courseId = $(this).data('id');
	  $.post($(this).data('cancelUrl')).done(function () {
	
	    $('.item-' + courseId).remove();
	  });
	});
	
	sortList({
	  element: '.course-list-group',
	  itemSelector: "li.course-item",
	  ajax: false
	});

/***/ })
]);