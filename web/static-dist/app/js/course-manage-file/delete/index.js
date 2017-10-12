webpackJsonp(["app/js/course-manage-file/delete/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $form = $('#material-delete-form');
	
	$('.material-delete-form-btn').click(function () {
	  $(this).button('loading').addClass('disabled');
	
	  var ids = [];
	  $('[data-role=batch-item]:checked').each(function () {
	    ids.push(this.value);
	  });
	
	  var isDeleteFile = $form.find('input[name="isDeleteFile"]:checked').val();
	  $.post($form.attr('action'), {
	    ids: ids,
	    isDeleteFile: isDeleteFile
	  }, function () {
	    window.location.reload();
	  });
	});

/***/ })
]);
//# sourceMappingURL=index.js.map