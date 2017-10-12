webpackJsonp(["app/js/course-manage/live-replay/upload/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _fileChoose = __webpack_require__("eca7a2561fa47d3f75f6");
	
	var _fileChoose2 = _interopRequireDefault(_fileChoose);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var fileChooser = new _fileChoose2["default"]();
	var $fileId = $('#material-file-chooser').find('[name=fileId]');
	fileChooser.on('select', function (file) {
	  $fileId.val(file.id);
	  _fileChoose2["default"].closeUI();
	  $('.jq-validate-error').remove();
	});
	
	$('.js-choose-trigger').click(function (event) {
	  _fileChoose2["default"].openUI();
	  $fileId.val('');
	});
	
	var $form = $('#replay-material-form');
	
	$form.validate({
	  rules: {
	    fileId: {
	      required: true
	    }
	  },
	  messages: {
	    fileId: Translator.trans('course.manage.live_replay_upload_error_hint')
	  }
	});

/***/ })
]);
//# sourceMappingURL=index.js.map