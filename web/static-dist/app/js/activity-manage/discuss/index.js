webpackJsonp(["app/js/activity-manage/discuss/index"],{

/***/ "98597ffe902676509dfc":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _editor = __webpack_require__("6ff75de42f89cafb6c75");
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Discuss = function () {
	  function Discuss(props) {
	    _classCallCheck(this, Discuss);
	
	    this._init();
	  }
	
	  _createClass(Discuss, [{
	    key: '_init',
	    value: function _init() {
	      this._inItStep2form();
	    }
	  }, {
	    key: '_inItStep2form',
	    value: function _inItStep2form() {
	      var $step2_form = $("#step2-form");
	      var validator = $step2_form.data('validator');
	      validator = $step2_form.validate({
	        rules: {
	          title: {
	            required: true,
	            maxlength: 50,
	            trim: true,
	            course_title: true
	          },
	          content: 'required'
	        }
	      });
	      (0, _editor.initEditor)($('[name="content"]'), validator);
	    }
	  }]);
	
	  return Discuss;
	}();
	
	exports["default"] = Discuss;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _discuss = __webpack_require__("98597ffe902676509dfc");
	
	var _discuss2 = _interopRequireDefault(_discuss);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _discuss2["default"]();

/***/ }),

/***/ "6ff75de42f89cafb6c75":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var initEditor = exports.initEditor = function initEditor($item, validator) {
	
	  var editor = CKEDITOR.replace('text-content-field', {
	    toolbar: 'Task',
	    filebrowserImageUploadUrl: $('#text-content-field').data('imageUploadUrl'),
	    filebrowserFlashUploadUrl: $('#text-content-field').data('flashUploadUrl'),
	    allowedContent: true,
	    height: 280
	  });
	
	  editor.on('change', function () {
	    console.log('change');
	    $item.val(editor.getData());
	    if (validator) {
	      validator.form();
	    }
	  });
	
	  //fix ie11 中文输入
	  editor.on('blur', function () {
	    console.log('blur');
	    $item.val(editor.getData());
	    if (validator) {
	      validator.form();
	    }
	  });
	
	  return editor;
	};

/***/ })

});
//# sourceMappingURL=index.js.map