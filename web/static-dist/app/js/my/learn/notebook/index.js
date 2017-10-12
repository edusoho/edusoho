webpackJsonp(["app/js/my/learn/notebook/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	$("#notebook-list").on('click', '.media', function () {
	  window.location.href = $(this).find('.notebook-go').attr('href');
	});
	
	var $notebook = $('#notebook');
	
	$notebook.on('click', '.notebook-note-collapsed', function () {
	  $(this).removeClass('notebook-note-collapsed');
	});
	
	$notebook.on('click', '.notebook-note-collapse-bar', function () {
	  $(this).parents('.notebook-note').addClass('notebook-note-collapsed');
	});
	
	$notebook.on('click', '.notebook-note-delete', function () {
	  var $btn = $(this);
	  if (!confirm(Translator.trans('course.notebook.delete_hint'))) {
	    return false;
	  }
	
	  $.post($btn.data('url'), function () {
	    $btn.parents('.notebook-note').remove();
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map