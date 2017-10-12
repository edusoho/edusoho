webpackJsonp(["app/js/question-manage/index"],{

/***/ "f637e828bcb096623369":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var DeleteAction = function () {
	  function DeleteAction($element, onSuccess) {
	    _classCallCheck(this, DeleteAction);
	
	    this.$element = $element;
	    this.onSuccess = onSuccess;
	    this.initEvent();
	  }
	
	  _createClass(DeleteAction, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '[data-role="item-delete"]', function (event) {
	        return _this._itemDelete(event);
	      });
	      this.$element.on('click', '[data-role="batch-delete"]', function (event) {
	        return _this._batchDelete(event);
	      });
	    }
	  }, {
	    key: '_itemDelete',
	    value: function _itemDelete(event) {
	      var $btn = $(event.currentTarget);
	
	      var name = $btn.data('name');
	      var message = $btn.data('message');
	      var self = this;
	
	      if (!message) {
	        message = Translator.trans('site.data.delete_name_hint', { 'name': name });
	      }
	
	      if (!confirm(message)) {
	        return;
	      }
	
	      $.post($btn.data('url'), function () {
	        if ($.isFunction(self.onSuccess)) {
	          self.onSuccess.call(self.$element);
	        } else {
	          $btn.closest('[data-role=item]').remove();
	          (0, _notify2["default"])('success', "删除成功");
	          window.location.reload();
	        }
	      });
	    }
	  }, {
	    key: '_batchDelete',
	    value: function _batchDelete(event) {
	      var $btn = $(event.currentTarget);
	      var name = $btn.data('name');
	
	      var ids = [];
	      this.$element.find('[data-role="batch-item"]:checked').each(function () {
	        ids.push(this.value);
	      });
	
	      if (ids.length == 0) {
	        (0, _notify2["default"])('danger', Translator.trans('site.data.uncheck_name_hint', { 'name': name }));
	        return;
	      }
	
	      if (!confirm(Translator.trans('site.data.delete_check_name_hint', { 'name': name }))) {
	        return;
	      }
	
	      (0, _notify2["default"])('info', Translator.trans('site.data.delete_submiting_hint'));
	
	      $.post($btn.data('url'), { ids: ids }, function () {
	        window.location.reload();
	      });
	    }
	  }]);
	
	  return DeleteAction;
	}();
	
	exports["default"] = DeleteAction;

/***/ }),

/***/ "4e3c732c4b4223e2d989":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var shortLongText = exports.shortLongText = function shortLongText($element) {
	  $element.on('click', '.short-text', function () {
	    var $short = $(this);
	    $short.slideUp('fast').parents('.short-long-text').find('.long-text').slideDown('fast');
	  });
	  $element.on('click', '.long-text', function () {
	    var $long = $(this);
	    $long.slideUp('fast').parents('.short-long-text').find('.short-text').slideDown('fast');
	  });
	};

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _batchSelect = __webpack_require__("de585ca0d3c2d0205c51");
	
	var _batchSelect2 = _interopRequireDefault(_batchSelect);
	
	var _deleteAction = __webpack_require__("f637e828bcb096623369");
	
	var _deleteAction2 = _interopRequireDefault(_deleteAction);
	
	var _shortLongText = __webpack_require__("4e3c732c4b4223e2d989");
	
	var _selectLinkage = __webpack_require__("1be2a74362f00ba903a0");
	
	var _selectLinkage2 = _interopRequireDefault(_selectLinkage);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	// new QuestionPicker($('#quiz-table-container'), $('#quiz-table'));
	// import QuestionPicker from '../../../common/component/question-picker';
	new _batchSelect2["default"]($('#quiz-table-container'));
	new _deleteAction2["default"]($('#quiz-table-container'));
	(0, _shortLongText.shortLongText)($('#quiz-table-container'));
	
	new _selectLinkage2["default"]($('[name="courseId"]'), $('[name="lessonId"]'));

/***/ })

});
//# sourceMappingURL=index.js.map