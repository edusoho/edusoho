webpackJsonp(["app/js/course/thread-form/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _attachmentActions = __webpack_require__("d5fb0e67d2d4c1ebaaed");
	
	var _attachmentActions2 = _interopRequireDefault(_attachmentActions);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $form = $('#thread-form');
	var validator = $form.validate({
	  rules: {
	    'title': {
	      required: true,
	      trim: true
	    },
	    'content': {
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
	
	var editor = CKEDITOR.replace('thread_content', {
	  toolbar: 'Thread',
	  filebrowserImageUploadUrl: $('#thread_content').data('imageUploadUrl')
	});
	
	editor.on('change', function () {
	  $('#thread_content').val(editor.getData());
	  validator.form();
	});
	editor.on('blur', function () {
	  $('#thread_content').val(editor.getData());
	  validator.form();
	});
	
	new _attachmentActions2["default"]($form);

/***/ })
]);
//# sourceMappingURL=index.js.map