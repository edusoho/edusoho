webpackJsonp(["app/js/open-course-manage/base/index"],{

/***/ "86a8d3a54811653afca1":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Base = function () {
	  function Base() {
	    _classCallCheck(this, Base);
	
	    this.initSelect2();
	    this.initCkeditor();
	    this.initValidator();
	    this.initCategory();
	  }
	
	  _createClass(Base, [{
	    key: 'initSelect2',
	    value: function initSelect2() {
	      var _$$select;
	
	      $('#course_tags').select2((_$$select = {
	        ajax: {
	          url: app.arguments.tagMatchUrl + '#',
	          dataType: 'json',
	          quietMillis: 100,
	          data: function data(term, page) {
	            return {
	              q: term,
	              page_limit: 10
	            };
	          },
	          results: function results(data) {
	            var results = [];
	            $.each(data, function (index, item) {
	
	              results.push({
	                id: item.name,
	                name: item.name
	              });
	            });
	
	            return {
	              results: results
	            };
	          }
	        },
	        initSelection: function initSelection(element, callback) {
	          var data = [];
	          $(element.val().split(",")).each(function () {
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
	          return Translator.trans('open_course.load_search_hint');
	        },
	        width: 'off',
	        multiple: true,
	        maximumSelectionSize: 20,
	        placeholder: Translator.trans('open_course.tag_required_hint')
	      }, _defineProperty(_$$select, 'width', 'off'), _defineProperty(_$$select, 'multiple', true), _defineProperty(_$$select, 'createSearchChoice', function createSearchChoice() {
	        return null;
	      }), _defineProperty(_$$select, 'maximumSelectionSize', 20), _$$select));
	    }
	  }, {
	    key: 'initValidator',
	    value: function initValidator() {
	      var $form = $('#course-form');
	      var validator = $form.validate({
	        rules: {
	          title: {
	            required: true
	          },
	          subtitle: {
	            required: true,
	            maxlength: 70
	          }
	        }
	      });
	
	      $('#course-create-btn').click(function () {
	        if (validator.form()) {
	          $form.submit();
	        }
	      });
	    }
	  }, {
	    key: 'initCkeditor',
	    value: function initCkeditor() {
	      if ($('#course-about-field').length > 0) {
	        CKEDITOR.replace('course-about-field', {
	          allowedContent: true,
	          toolbar: 'Detail',
	          filebrowserImageUploadUrl: $('#course-about-field').data('imageUploadUrl')
	        });
	      }
	    }
	  }, {
	    key: 'initCategory',
	    value: function initCategory() {
	      $('[data-role="tree-select"], [name="categoryId"]').select2({
	        treeview: true,
	        dropdownAutoWidth: true,
	        treeviewInitState: 'collapsed',
	        placeholderOption: 'first'
	        // treeviewInitState: 'expanded'
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
	
	var _base = __webpack_require__("86a8d3a54811653afca1");
	
	var _base2 = _interopRequireDefault(_base);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _base2["default"]();

/***/ })

});
//# sourceMappingURL=index.js.map