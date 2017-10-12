webpackJsonp(["app/js/classroom/thread-form/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _attachmentActions = __webpack_require__("d5fb0e67d2d4c1ebaaed");
	
	var _attachmentActions2 = _interopRequireDefault(_attachmentActions);
	
	var _esWebuploader = __webpack_require__("0f84c916401868c4758e");
	
	var _esWebuploader2 = _interopRequireDefault(_esWebuploader);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
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
	
	var threadType = $form.find('[name="type"]').val();
	
	if (threadType == 'event') {
	  $form.find('[name="maxUsers"]').rules('add', {
	    positive_integer: true
	  });
	  $form.find('[name="location"]').rules('add', {
	    visible_character: true
	  });
	  $form.find('[name="startTime"]').rules('add', {
	    required: true,
	    DateAndTime: true
	  });
	
	  $form.find('[name="startTime"]').datetimepicker({
	    language: document.documentElement.lang,
	    autoclose: true,
	    format: 'yyyy-mm-dd hh:ii',
	    minView: 'hour'
	  }).on('hide', function (ev) {
	    $form.validate('[name=startTime]');
	  });
	  $form.find('[name="startTime"]').datetimepicker('setStartDate', new Date());
	
	  new _esWebuploader2["default"]({
	    element: '#js-activity-uploader',
	    onUploadSuccess: function onUploadSuccess(file, response) {
	      $form.find('[name=actvityPicture]').val(response.url);
	      (0, _notify2["default"])('success', Translator.trans('site.upload_success_hint'));
	    }
	  });
	}
	
	new _attachmentActions2["default"]($form);

/***/ })
]);
//# sourceMappingURL=index.js.map