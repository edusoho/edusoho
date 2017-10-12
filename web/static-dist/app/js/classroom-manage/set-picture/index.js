webpackJsonp(["app/js/classroom-manage/set-picture/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _esWebuploader = __webpack_require__("0f84c916401868c4758e");
	
	var _esWebuploader2 = _interopRequireDefault(_esWebuploader);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Cover = function () {
	  function Cover() {
	    _classCallCheck(this, Cover);
	
	    this.init();
	  }
	
	  _createClass(Cover, [{
	    key: 'init',
	    value: function init() {
	      new _esWebuploader2["default"]({
	        element: '#upload-picture-btn',
	        onUploadSuccess: function onUploadSuccess(file, response) {
	          var url = $("#upload-picture-btn").data("gotoUrl");
	          (0, _notify2["default"])('success', Translator.trans('site.upload_success_hint'));
	          document.location.href = url;
	        }
	      });
	    }
	  }]);
	
	  return Cover;
	}();
	
	new Cover();

/***/ })
]);
//# sourceMappingURL=index.js.map