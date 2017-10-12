webpackJsonp(["app/js/courseset-manage/base/index"],{

/***/ "c6883cd284506260d98b":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Base = function () {
	  function Base() {
	    _classCallCheck(this, Base);
	
	    this.init();
	  }
	
	  _createClass(Base, [{
	    key: 'init',
	    value: function init() {
	      this.initValidator();
	      this.initTags();
	    }
	  }, {
	    key: 'initValidator',
	    value: function initValidator() {
	      var $form = $('#courseset-form');
	      var validator = $form.validate({
	        rules: {
	          title: {
	            maxlength: 100,
	            required: {
	              depends: function depends() {
	                $(this).val($.trim($(this).val()));
	                return true;
	              }
	            },
	            course_title: true
	          },
	          subtitle: {
	            required: {
	              depends: function depends() {
	                $(this).val($.trim($(this).val()));
	                return false;
	              }
	            },
	            course_title: true
	          }
	        }
	      });
	      $('#courseset-base-submit').click(function (event) {
	        if (validator.form()) {
	          $(event.currentTarget).button('loading');
	          $form.submit();
	        }
	      });
	    }
	  }, {
	    key: 'initTags',
	    value: function initTags() {
	      var $tags = $('#tags');
	      $tags.select2({
	        ajax: {
	          url: $tags.data('url'),
	          dataType: 'json',
	          quietMillis: 500,
	          data: function data(term, page) {
	            return {
	              q: term,
	              page_limit: 10
	            };
	          },
	          results: function results(data) {
	            console.log(data);
	            return {
	              results: data.map(function (item) {
	                return { id: item.name, name: item.name };
	              })
	            };
	          }
	        },
	        initSelection: function initSelection(element, callback) {
	          var data = [];
	          $(element.val().split(',')).each(function () {
	            data.push({
	              id: this,
	              name: this
	            });
	          });
	          callback(data);
	        },
	        formatSelection: function formatSelection(item) {
	          return item.name;
	        },
	        formatResult: function formatResult(item) {
	          return item.name;
	        },
	
	        formatSearching: function formatSearching() {
	          return Translator.trans('site.searching_hint');
	        },
	        multiple: true,
	        maximumSelectionSize: 20,
	        placeholder: Translator.trans('course_set.manage.tag_required_hint'),
	        width: 'off',
	        createSearchChoice: function createSearchChoice() {
	          return null;
	        }
	      });
	    }
	  }]);
	
	  return Base;
	}();
	
	exports["default"] = Base;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _base = __webpack_require__("c6883cd284506260d98b");
	
	var _base2 = _interopRequireDefault(_base);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _base2["default"]();

/***/ })

});
//# sourceMappingURL=index.js.map