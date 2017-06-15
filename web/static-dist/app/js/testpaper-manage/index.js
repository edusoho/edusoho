webpackJsonp(["app/js/testpaper-manage/index"],[
/* 0 */
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import notify from 'common/notify';
	import BatchSelect from '../../common/widget/batch-select';
	import DeleteAction from '../../common/widget/delete-action';
	
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
	        notify('success', $target.text() + "成功");
	        $tr.replaceWith(html);
	      }).error(function () {
	        notify('danger', $target.text() + "失败");
	      });
	    }
	  }]);
	
	  return TestpaperManage;
	}();
	
	var $container = $('#quiz-table-container');
	new TestpaperManage($container);
	new BatchSelect($container);
	new DeleteAction($container);

/***/ })
]);