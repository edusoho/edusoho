webpackJsonp(["app/js/task/plugin/note/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _saveRedmine = __webpack_require__("4e9506cac544b82346a8");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var heigth = $('.js-sidebar-pane').height() - 175;
	var $content = $('#note-content-field');
	var lastNoteContent = void 0;
	var editor = CKEDITOR.replace('note-content-field', {
	  toolbar: 'Simple',
	  filebrowserImageUploadUrl: $content.data('imageUploadUrl'),
	  allowedContent: true,
	  height: heigth < 300 ? 200 : heigth
	});
	
	editor.on('change', function () {
	  $content.val(editor.getData());
	});
	
	$('#note-save-btn').click(function (event) {
	  var $btn = $(this);
	  event.preventDefault();
	  saveNote($btn);
	});
	
	setInterval(saveNote, 30000);
	
	function saveNote() {
	  var $btn = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	
	  if (!$.trim($content.val())) {
	    $btn ? (0, _notify2["default"])('danger', '请输入笔记内容！') : '';
	    return;
	  }
	  var $form = $('#task-note-plugin-form');
	  var data = $form.serializeArray();
	  if (lastNoteContent === data[0].value) {
	    return;
	  }
	  (0, _saveRedmine.saveRedmineLoading)();
	  $btn ? $btn.attr('disabled', 'disabled') : "";
	  $.post($form.attr('action'), data).then(function (response) {
	    (0, _saveRedmine.saveRedmineSuccess)();
	    if ($btn) {
	      $btn.removeAttr('disabled');
	    }
	    lastNoteContent = data[0].value;
	  });
	}

/***/ }),

/***/ "4e9506cac544b82346a8":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	exports.saveRedmineSuccess = exports.saveRedmineLoading = undefined;
	
	var _unit = __webpack_require__("3c398f87808202f19beb");
	
	var $savedMessage = $('[data-role=saved-message]');
	(0, _unit.dateFormat)();
	var saveRedmineLoading = function saveRedmineLoading() {
	  $savedMessage.html(Translator.trans('task.plugin_redmine_save_hint')).show();
	};
	
	var saveRedmineSuccess = function saveRedmineSuccess() {
	  var date = new Date().Format('yyyy-MM-dd hh:mm:ss');
	  $savedMessage.html(Translator.trans('task.plugin_redmine_save_success_hint', { date: date })).show();
	  setTimeout(function () {
	    $savedMessage.hide();
	  }, 3000);
	};
	
	exports.saveRedmineLoading = saveRedmineLoading;
	exports.saveRedmineSuccess = saveRedmineSuccess;

/***/ })

});
//# sourceMappingURL=index.js.map