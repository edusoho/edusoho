webpackJsonp(["app/js/course/explore/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	echo.init();
	
	$('#live, #free').on('click', function (event) {
	  var $this = $(event.currentTarget);
	  $('input:checkbox').attr('checked', false);
	  $this.attr('checked', true);
	
	  window.location.href = $this.val();
	});
	
	$(".open-course-list").on('click', '.section-more-btn a', function (event) {
	  var url = $(undefined).attr('data-url');
	  $.ajax({
	    url: url,
	    dataType: 'html',
	    success: function success(html) {
	      var content = $('.open-course-list .course-block,.open-course-list .section-more-btn', $(html)).fadeIn('slow');
	      $(".section-more-btn").remove();
	      $('.open-course-list').append(content);
	      echo.init();
	    }
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map