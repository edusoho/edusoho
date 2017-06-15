webpackJsonp(["app/js/classroom-manage/course-manage/index"],[
/* 0 */
/***/ (function(module, exports) {

	import sortList from 'common/sortable';
	import notify from "common/notify";
	
	$(".course-list-group").on('click', '.close', function () {
	  if (confirm('是否要从班级移除该课程？')) {
	    $.post($(this).data('url'), function (resp) {
	      if (resp.success) {
	        notify('success', Translator.trans('课程移除成功!'));
	        window.location.reload();
	      } else {
	        notify('danger', Translator.trans('操作失败:') + resp.message);
	      }
	    });
	  }
	});
	
	sortList({
	  element: '#course-list-group',
	  itemSelector: "li",
	  ajax: false
	}, function (data) {
	  $('#courses-form').submit();
	});

/***/ })
]);