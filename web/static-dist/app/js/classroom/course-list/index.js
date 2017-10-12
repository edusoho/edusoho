webpackJsonp(["app/js/classroom/course-list/index"],{

/***/ "d14d05cad9e7abf02a5d":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var toggleIcon = exports.toggleIcon = function toggleIcon(target, $expandIconClass, $putIconClass) {
	  var $icon = target.find('.js-remove-icon');
	  var $text = target.find('.js-remove-text');
	  if ($icon.hasClass($expandIconClass)) {
	    $icon.removeClass($expandIconClass).addClass($putIconClass);
	    $text ? $text.text(Translator.trans('收起')) : '';
	  } else {
	    $icon.removeClass($putIconClass).addClass($expandIconClass);
	    $text ? $text.text(Translator.trans('展开')) : '';
	  }
	};
	
	var chapterAnimate = exports.chapterAnimate = function chapterAnimate() {
	  var delegateTarget = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'body';
	  var target = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '.js-task-chapter';
	  var $expandIconClass = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'es-icon-remove';
	  var $putIconClass = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'es-icon-anonymous-iconfont';
	
	  $(delegateTarget).on('click', target, function (event) {
	    var $this = $(event.currentTarget);
	    $this.nextUntil(target).animate({ height: 'toggle', opacity: 'toggle' }, "normal");
	    toggleIcon($this, $expandIconClass, $putIconClass);
	  });
	};

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _list = __webpack_require__("0b848e831f89a0e555eb");
	
	var _list2 = _interopRequireDefault(_list);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _list2["default"]($('.class-course-list'));

/***/ }),

/***/ "0b848e831f89a0e555eb":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _chapterAnimate = __webpack_require__("d14d05cad9e7abf02a5d");
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var CourseList = function () {
	  function CourseList($element) {
	    _classCallCheck(this, CourseList);
	
	    this.$element = $element;
	    (0, _chapterAnimate.chapterAnimate)();
	    this.initEvent();
	    echo.init();
	  }
	
	  _createClass(CourseList, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '.es-icon-keyboardarrowdown', function (event) {
	        return _this.onExpandCourse(event);
	      });
	      this.$element.on('click', '.es-icon-keyboardarrowup', function (event) {
	        return _this.onCollapseCourse(event);
	      });
	    }
	  }, {
	    key: 'onExpandCourse',
	    value: function onExpandCourse(e) {
	      var $target = $(e.currentTarget);
	      var $parent = $target.parents(".course-item");
	      var $lessonList = $target.parents(".media").siblings(".course-detail-content");
	      if ($lessonList.length > 0) {
	        this._lessonListSHow($lessonList);
	      } else {
	        var self = this;
	        $.get($target.data('lessonUrl'), { 'visibility': 0 }, function (html) {
	          $parent.append(html);
	          self._lessonListSHow($parent.siblings(".course-detail-content"));
	        });
	      }
	
	      $target.addClass('es-icon-keyboardarrowup').removeClass('es-icon-keyboardarrowdown');
	    }
	  }, {
	    key: 'onCollapseCourse',
	    value: function onCollapseCourse(e) {
	      var $target = $(e.currentTarget);
	      this._lessonListSHow($target.parents(".media").siblings(".course-detail-content"));
	      $target.addClass('es-icon-keyboardarrowdown').removeClass('es-icon-keyboardarrowup');
	    }
	  }, {
	    key: '_lessonListSHow',
	    value: function _lessonListSHow($list) {
	      if ($list.length > 0) {
	        $list.animate({
	          visibility: 'toggle',
	          opacity: 'toggle',
	          easing: 'linear'
	        });
	        $list.height();
	      }
	    }
	  }]);
	
	  return CourseList;
	}();
	
	exports["default"] = CourseList;

/***/ })

});
//# sourceMappingURL=index.js.map