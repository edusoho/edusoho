webpackJsonp(["app/js/courseset-manage/detail/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _reactDom = __webpack_require__("5fdcf1aea784583ca083");
	
	var _reactDom2 = _interopRequireDefault(_reactDom);
	
	var _react = __webpack_require__("33a776824bec073629e5");
	
	var _react2 = _interopRequireDefault(_react);
	
	var _multiInput = __webpack_require__("26fa658edb0135ccf5db");
	
	var _multiInput2 = _interopRequireDefault(_multiInput);
	
	var _postal = __webpack_require__("ae88c18278ce1387fd20");
	
	var _postal2 = _interopRequireDefault(_postal);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var detail = function () {
	  function detail() {
	    _classCallCheck(this, detail);
	
	    this.init();
	  }
	
	  _createClass(detail, [{
	    key: 'init',
	    value: function init() {
	      this.initCkeditor();
	      this.renderMultiGroupComponent('course-goals', 'goals');
	      this.renderMultiGroupComponent('intended-students', 'audiences');
	      this.submitForm();
	    }
	  }, {
	    key: 'initCkeditor',
	    value: function initCkeditor() {
	      CKEDITOR.replace('summary', {
	        allowedContent: true,
	        toolbar: 'Detail',
	        filebrowserImageUploadUrl: $('#courseset-summary-field').data('imageUploadUrl')
	      });
	    }
	  }, {
	    key: 'renderMultiGroupComponent',
	    value: function renderMultiGroupComponent(elementId, name) {
	      var datas = $('#' + elementId).data('init-value');
	      _reactDom2["default"].render(_react2["default"].createElement(_multiInput2["default"], {
	        blurIsAdd: true,
	        sortable: true,
	        dataSource: datas,
	        inputName: name + "[]",
	        outputDataElement: name }), document.getElementById(elementId));
	    }
	  }, {
	    key: 'submitForm',
	    value: function submitForm() {
	      var _this = this;
	
	      $('#courseset-submit').click(function (event) {
	        _this.publishAddMessage();
	        $(event.currentTarget).button('loading');
	        $('#courseset-detail-form').submit();
	      });
	    }
	  }, {
	    key: 'publishAddMessage',
	    value: function publishAddMessage() {
	      _postal2["default"].publish({
	        channel: "courseInfoMultiInput",
	        topic: "addMultiInput"
	      });
	    }
	  }]);
	
	  return detail;
	}();
	
	new detail();

/***/ })
]);
//# sourceMappingURL=index.js.map