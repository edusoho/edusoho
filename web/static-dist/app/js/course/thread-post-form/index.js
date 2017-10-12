webpackJsonp(["app/js/course/thread-post-form/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var $form = $('#thread-post-form');
	
	var validator = $form.validate({
	  rules: {
	    'post[content]': {
	      required: true
	    }
	  }
	});
	
	$('.js-btn-thread-save').click(function (event) {
	  if (validator.form()) {
	    $(event.currentTarget).button('loading');
	    $form.submit();
	  }
	});
	
	var editor = CKEDITOR.replace('post_content', {
	  toolbar: 'Thread',
	  filebrowserImageUploadUrl: $('#post_content').data('imageUploadUrl'),
	  height: 300
	});
	
	editor.on('change', function () {
	  $('#post_content').val(editor.getData());
	  validator.form();
	});
	
	editor.on('blur', function () {
	  $('#post_content').val(editor.getData());
	  validator.form();
	});

/***/ })
]);
//# sourceMappingURL=index.js.map