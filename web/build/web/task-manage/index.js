webpackJsonp([23],{

/***/ 0:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	var _help = __webpack_require__(112);

	(0, _help.deleteTask)();
	(0, _help.sortList)();

	//

/***/ },

/***/ 112:
/***/ function(module, exports, __webpack_require__) {

	'use strict';

	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	exports.sortList = exports.deleteTask = undefined;

	var _notify = __webpack_require__(110);

	var _notify2 = _interopRequireDefault(_notify);

	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

	var deleteTask = exports.deleteTask = function deleteTask() {
	  $('body').on('click', '.delete-task', function (evt) {
	    if (!confirm(Translator.trans('是否确定删除任务？'))) return;
	    $.post($(evt.target).data('url'), function (data) {
	      console.log(data);
	      if (data.success) {
	        (0, _notify2.default)('success', "删除成功");
	        location.reload();
	      } else {
	        (0, _notify2.default)('danger', "删除失败");
	      }
	    });
	  });
	};

	var sortList = exports.sortList = function sortList() {
	  var $list = $("#sortable-list").sortable({
	    distance: 20,
	    itemSelector: 'li.drag',
	    onDrop: function onDrop(item, container, _super) {
	      _super(item, container);
	      var data = $list.sortable("serialize").get();
	      console.log(data);
	      //排序URL
	      // $.post($list.data('sortUrl'), {ids:data}, function(response){

	      // });
	    },
	    serialize: function serialize(parent, children, isContainer) {
	      return isContainer ? children : parent.attr('id');
	    }
	  });
	};

	exports.default = {
	  deleteTask: deleteTask,
	  sortList: sortList
	};

/***/ }

});