webpackJsonp(["app/js/courseset/create/index"],{

/***/ "f9fb8354c8bd8e47ad7e":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Create = function () {
	  function Create($element) {
	    _classCallCheck(this, Create);
	
	    this.$element = $element;
	    this.$courseSetType = this.$element.find('.js-courseSetType');
	    this.$currentCourseSetType = this.$element.find('.js-courseSetType.active');;
	    this.init();
	  }
	
	  _createClass(Create, [{
	    key: 'init',
	    value: function init() {
	      var _this = this;
	
	      this.validator = this.$element.validate({
	        rules: {
	          title: {
	            maxlength: 100,
	            required: true,
	            trim: true,
	            course_title: true
	          }
	        },
	        messages: {
	          title: {
	            required: Translator.trans('course_set.title_required_error_hint'),
	            trim: Translator.trans('course_set.title_required_error_hint')
	          }
	        }
	      });
	
	      this.$courseSetType.click(function (event) {
	        _this.$courseSetType.removeClass('active');
	        _this.$currentCourseSetType = $(event.currentTarget).addClass('active');
	        $('input[name="type"]').val(_this.$currentCourseSetType.data('type'));
	        var $title = $('#course_title');
	        $title.rules('remove');
	        if (_this.$currentCourseSetType.data('type') != 'live') {
	          $title.rules("add", {
	            required: true,
	            trim: true,
	            course_title: true
	          });
	        } else {
	          $title.rules("add", {
	            required: true,
	            trim: true,
	            open_live_course_title: true
	          });
	        }
	      });
	    }
	  }]);
	
	  return Create;
	}();
	
	exports["default"] = Create;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _create = __webpack_require__("f9fb8354c8bd8e47ad7e");
	
	var _create2 = _interopRequireDefault(_create);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _create2["default"]($('#courseset-create-form'));

/***/ })

});
//# sourceMappingURL=index.js.map