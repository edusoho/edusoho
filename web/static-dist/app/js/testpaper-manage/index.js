webpackJsonp(["app/js/testpaper-manage/index"],{

/***/ "de585ca0d3c2d0205c51":
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var BatchSelect = function () {
	  function BatchSelect($element) {
	    _classCallCheck(this, BatchSelect);
	
	    this.$element = $element;
	    this.initEvent();
	  }
	
	  _createClass(BatchSelect, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '[data-role="batch-select"]', function (event) {
	        return _this._batch2Item(event);
	      });
	      this.$element.on('click', '[data-role="batch-item"]', function (event) {
	        return _this._item2Batch(event);
	      });
	    }
	  }, {
	    key: '_batch2Item',
	    value: function _batch2Item(event) {
	      var checked = $(event.currentTarget).prop('checked');
	      this.$element.find('[data-role="batch-select"]').prop('checked', checked);
	      this.$element.find('[data-role="batch-item"]:visible').prop('checked', checked);
	    }
	  }, {
	    key: '_item2Batch',
	    value: function _item2Batch(event) {
	      var itemLength = this.$element.find('[data-role="batch-item"]').length;
	      var itemCheckedLength = this.$element.find('[data-role="batch-item"]:checked').length;
	
	      if (itemLength == itemCheckedLength) {
	        this.$element.find('[data-role="batch-select"]').prop('checked', true);
	      } else {
	        this.$element.find('[data-role="batch-select"]').prop('checked', false);
	      }
	    }
	  }]);
	
	  return BatchSelect;
	}();
	
	export default BatchSelect;

/***/ }),

/***/ "f637e828bcb096623369":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
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
	        message = '真的要删除该' + name + '吗？';
	      }
	
	      if (!confirm(message)) {
	        return;
	      }
	
	      $.post($btn.data('url'), function () {
	        if ($.isFunction(self.onSuccess)) {
	          self.onSuccess.call(self.$element);
	        } else {
	          $btn.closest('[data-role=item]').remove();
	          (0, _notify2.default)('success', "删除成功");
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
	        (0, _notify2.default)('danger', '未选中任何' + name);
	        return;
	      }
	
	      if (!confirm('确定要删除选中的条' + name + '吗？')) {
	        return;
	      }
	
	      (0, _notify2.default)('info', '正在删除...');
	
	      $.post($btn.data('url'), { ids: ids }, function () {
	        window.location.reload();
	      });
	    }
	  }]);
	
	  return DeleteAction;
	}();
	
	exports.default = DeleteAction;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _batchSelect = __webpack_require__("de585ca0d3c2d0205c51");
	
	var _batchSelect2 = _interopRequireDefault(_batchSelect);
	
	var _deleteAction = __webpack_require__("f637e828bcb096623369");
	
	var _deleteAction2 = _interopRequireDefault(_deleteAction);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var TestpaperManage = function () {
	  function TestpaperManage($container) {
	    _classCallCheck(this, TestpaperManage);
	
	    this.$container = $container;
	    this._initEvent();
	    this._init();
	  }
	
	  _createClass(TestpaperManage, [{
	    key: '_initEvent',
	    value: function _initEvent() {
	      var _this = this;
	
	      this.$container.on('click', '.open-testpaper,.close-testpaper', function (event) {
	        return _this.testpaperAction(event);
	      });
	    }
	  }, {
	    key: '_init',
	    value: function _init() {}
	  }, {
	    key: 'testpaperAction',
	    value: function testpaperAction(event) {
	      var $target = $(event.currentTarget);
	      var $tr = $target.closest('tr');
	
	      if (!confirm($target.attr('title'))) {
	        return;
	      }
	
	      $.post($target.data('url'), function (html) {
	        (0, _notify2.default)('success', $target.text() + "成功");
	        $tr.replaceWith(html);
	      }).error(function () {
	        (0, _notify2.default)('danger', $target.text() + "失败");
	      });
	    }
	  }]);
	
	  return TestpaperManage;
	}();
	
	var $container = $('#quiz-table-container');
	new TestpaperManage($container);
	new _batchSelect2.default($container);
	new _deleteAction2.default($container);

/***/ })

});