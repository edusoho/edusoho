webpackJsonp(["app/js/course-manage/info/index"],[
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
	
	var courseInfo = function () {
	  function courseInfo() {
	    _classCallCheck(this, courseInfo);
	
	    this.init();
	  }
	
	  _createClass(courseInfo, [{
	    key: 'init',
	    value: function init() {
	      if ($('#maxStudentNum-field').length > 0) {
	        $.get($('#maxStudentNum-field').data('liveCapacityUrl')).done(function (liveCapacity) {
	          $('#maxStudentNum-field').data('liveCapacity', liveCapacity.capacity);
	        });
	      }
	      this.initCkeidtor();
	      this.initValidator();
	      this.checkBoxChange();
	      this.initDatePicker('#expiryStartDate');
	      this.initDatePicker('#expiryEndDate');
	      this.renderMultiGroupComponent('course-goals', 'goals');
	      this.renderMultiGroupComponent('intended-students', 'audiences');
	    }
	  }, {
	    key: 'initCkeidtor',
	    value: function initCkeidtor() {
	      CKEDITOR.replace('summary', {
	        allowedContent: true,
	        toolbar: 'Detail',
	        filebrowserImageUploadUrl: $('#summary').data('imageUploadUrl')
	      });
	    }
	  }, {
	    key: 'renderMultiGroupComponent',
	    value: function renderMultiGroupComponent(elementId, name) {
	      var datas = $('#' + elementId).data('init-value');
	      _reactDom2["default"].render(_react2["default"].createElement(_multiInput2["default"], {
	        dataSource: datas,
	        outputDataElement: name }), document.getElementById(elementId));
	    }
	  }, {
	    key: 'initValidator',
	    value: function initValidator() {
	      var _this = this;
	
	      var $form = $('#course-info-form');
	      var validator = $form.validate({
	        currentDom: '#course-submit',
	        groups: {
	          date: 'expiryStartDate expiryEndDate'
	        },
	        rules: {
	          title: {
	            maxlength: 100,
	            required: {
	              depends: function depends() {
	                $(this).val($.trim($(this).val()));
	                return true;
	              }
	            }
	          },
	          maxStudentNum: {
	            required: true,
	            live_capacity: true,
	            positive_integer: true
	          },
	          expiryDays: {
	            required: function required() {
	              return $('input[name="expiryMode"]:checked').val() != 'date';
	            },
	            digits: true,
	            max_year: true
	          },
	          expiryStartDate: {
	            required: function required() {
	              return $('input[name="expiryMode"]:checked').val() == 'date';
	            },
	            date: true,
	            before_date: '#expiryEndDate'
	          },
	          expiryEndDate: {
	            required: function required() {
	              return $('input[name="expiryMode"]:checked').val() == 'date';
	            },
	            date: true,
	            after_date: '#expiryStartDate'
	          }
	        },
	        messages: {
	          title: {
	            require: Translator.trans('course.manage.title_required_error_hint')
	          },
	          maxStudentNum: {
	            required: Translator.trans('course.manage.max_student_num_error_hint')
	          },
	          expiryDays: {
	            required: Translator.trans('course.manage.deadline_end_date_error_hint')
	          },
	          expiryStartDate: {
	            required: Translator.trans('course.manage.expiry_start_date_error_hint'),
	            before: Translator.trans('course.manage.expiry_days_error_hint')
	          },
	          expiryEndDate: {
	            required: Translator.trans('course.manage.expiry_end_date_error_hint'),
	            after: Translator.trans('course.manage.expiry_start_date_error_hint')
	          }
	        }
	      });
	
	      $.validator.addMethod("before", function (value, element, params) {
	        if ($('input[name="expiryMode"]:checked').val() !== 'date') {
	          return true;
	        }
	        return !value || $(params).val() > value;
	      }, Translator.trans('course.manage.expiry_end_date_error_hint'));
	
	      $.validator.addMethod("after", function (value, element, params) {
	        if ($('input[name="expiryMode"]:checked').val() !== 'date') {
	          return true;
	        }
	        return !value || $(params).val() < value;
	      }, Translator.trans('course.manage.expiry_start_date_error_hint'));
	
	      $('#course-submit').click(function () {
	        if (validator.form()) {
	          _this.publishAddMessage();
	          $form.submit();
	        }
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
	  }, {
	    key: 'initDatePicker',
	    value: function initDatePicker($id) {
	      var $picker = $($id);
	      $picker.datetimepicker({
	        format: 'yyyy-mm-dd',
	        language: document.documentElement.lang,
	        minView: 2, //month
	        autoclose: true,
	        endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
	      });
	      $picker.datetimepicker('setStartDate', new Date());
	    }
	  }, {
	    key: 'checkBoxChange',
	    value: function checkBoxChange() {
	      $('input[name="expiryMode"]').on('change', function (event) {
	        if ($('input[name="expiryMode"]:checked').val() == 'date') {
	          $('#expiry-days').removeClass('hidden').addClass('hidden');
	          $('#expiry-date').removeClass('hidden');
	        } else {
	          $('#expiry-date').removeClass('hidden').addClass('hidden');
	          $('#expiry-days').removeClass('hidden');
	        }
	      });
	    }
	  }]);
	
	  return courseInfo;
	}();
	
	new courseInfo();
	
	jQuery.validator.addMethod("max_year", function (value, element) {
	  return this.optional(element) || value < 100000;
	}, Translator.trans("course.manage.max_year_error_hint"));
	
	jQuery.validator.addMethod("live_capacity", function (value, element) {
	  var maxCapacity = parseInt($(element).data('liveCapacity'));
	  if (value > maxCapacity) {
	    var message = Translator.trans('course.manage.max_capacity_hint', { capacity: maxCapacity });
	    $(element).parent().siblings('.js-course-rule').find('p').html(message);
	  } else {
	    $(element).parent().siblings('.js-course-rule').find('p').html('');
	  }
	
	  return true;
	});

/***/ })
]);
//# sourceMappingURL=index.js.map